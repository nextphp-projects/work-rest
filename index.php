<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/bootstrap.php';


// Kullanıcı tarafından ayarlanması gereken parametreler
$config = [
    'baseUri' => '/work-rest',
    'allowedOrigins' => [
        'http://allowed-origin.com' => ['GET', 'POST'],
        'http://another-allowed-origin.com' => ['GET', 'PUT'],
        '*' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD', 'TRACE', 'CONNECT', 'PRI']
    ],
];
$logFilePath = __DIR__ . '/../logs/app.log';
$controllers = [
    'NextPHP\App\Resource\CrudResource',
    'NextPHP\App\Resource\TestResource',
];

// Router'ı başlat
$router = initializeApp($config, $controllers, $logFilePath);

// İsteği işle ve response'u gönder
handleRequest($router);