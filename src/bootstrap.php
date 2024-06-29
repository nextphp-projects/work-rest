<?php

/**
 * This file is part of the NextPHP REST package.
 *
 * (c) [Your Name] <your.email@example.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @license https://opensource.org/licenses/MIT MIT License
 */

use NextPHP\Rest\Http\Router;
use NextPHP\Rest\Logging\SimpleLogger;
use NextPHP\Rest\Http\Request;
use NextPHP\Rest\Http\Response;

/**
 * Initialize the application with necessary configurations and dependencies.
 *
 * @param array $config The application configuration.
 * @param array $controllers The list of controller classes.
 * @param string $logFilePath The path for the log file.
 * @return Router
 */
function initializeApp(array $config, array $controllers, string $logFilePath): Router
{
    // Logger'ı oluştur
    $logger = new SimpleLogger($logFilePath);

    // Router'ı oluştur
    $router = new Router($config, $logger);

    // Route'ları kaydet
    foreach ($controllers as $controller) {
        $router->registerRoutesFromController($controller);
    }

    return $router;
}

/**
 * Handle the incoming HTTP request and send the response.
 *
 * @param Router $router The router instance.
 */
function handleRequest(Router $router): void
{
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
}