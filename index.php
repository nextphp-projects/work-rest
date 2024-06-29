<?php

require_once __DIR__ . '/vendor/autoload.php';

use NextPHP\Rest\Http\Router;
use NextPHP\Rest\Http\Request;
use NextPHP\Rest\Http\Response;
use NextPHP\Rest\Http\Middleware\AuthMiddleware;
use NextPHP\App\Resource\CrudResource;
use NextPHP\App\Resource\TestResource;

$router = new Router([
    'baseUri' => '/work-rest',
    'allowedOrigins' => [
        'http://allowed-origin.com' => ['GET', 'POST'],
        'http://another-allowed-origin.com' => ['GET', 'PUT'],
        '*' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD', 'TRACE', 'CONNECT', 'PRI']
    ]
]);

// Register routes from controllers
//$router->registerRoutesFromController('NextPHP\App\Resource\UserResource');
//$router->registerRoutesFromController('NextPHP\App\Resource\TestResource');

$router->registerRoutesFromController(CrudResource::class);
$router->registerRoutesFromController(TestResource::class);

// URI ve istek verilerini al
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Kendi oluşturduğunuz Request ve Response nesneleri
$request = new Request($method, $uri, getallheaders(), file_get_contents('php://input'), $_GET, $_POST);
$response = new Response();

// Yönlendirme işlemini başlat
$response = $router->dispatch($request, $response);

// Yanıtı gönder
if ($response) {
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $value) {
        header("$name: $value");
    }
    echo $response->getBody();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => 'No response returned.']);
}

/*
require_once __DIR__ . '/vendor/autoload.php';

use NextPHP\App\Resource\UserResource;
use NextPHP\App\Resource\TestResource;

use NextPHP\Rest\Http\Router;
use NextPHP\Rest\Http\Request;
use NextPHP\Rest\Http\Response;

ob_start();

$router = new Router([
    'baseUri' => '/rest',
    'allowedOrigins' => [
        'http://allowed-origin.com' => ['GET', 'POST'],
        'http://another-allowed-origin.com' => ['GET', 'PUT'],
        '*' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD', 'TRACE', 'CONNECT', 'PRI'] // '*' tüm kaynakları kabul eder
    ]
]);

$router->registerRoutesFromController(UserResource::class);
$router->registerRoutesFromController(TestResource::class);

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Kendi oluşturduğunuz Request ve Response nesneleri
$request = new Request($method, $uri, getallheaders(), file_get_contents('php://input'), $_GET, $_POST);
$response = new Response();

// Yönlendirme işlemini başlatın
$response = $router->dispatch($request, $response);

// Yanıtı gönderin
if ($response) {
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $value) {
        header("$name: $value");
    }
    echo $response->getBody();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => 'No response returned.']);
}

ob_end_flush();
*/