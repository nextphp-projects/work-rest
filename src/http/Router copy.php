<?php

namespace NextPHP\Rest\Http;

use NextPHP\Rest\Http\Request;
use NextPHP\Rest\Http\Response;
use NextPHP\Rest\Http\Attributes\Get;
use NextPHP\Rest\Http\Attributes\Post;
use NextPHP\Rest\Http\Attributes\Put;
use NextPHP\Rest\Http\Attributes\Delete;
use NextPHP\Rest\Http\Attributes\Patch;
use NextPHP\Rest\Http\Attributes\Options;
use NextPHP\Rest\Http\Attributes\Head;
use NextPHP\Rest\Http\Attributes\Trace;
use NextPHP\Rest\Http\Attributes\Connect;
use NextPHP\Rest\Http\Attributes\Pri;
use NextPHP\Rest\Http\Attributes\RouteGroup;
use NextPHP\Rest\Http\Attributes\Middleware;
use ReflectionClass;
use ReflectionMethod;

class Router2
{
    private $routes = [];
    private $prefix = '';
    private $baseUri = '';
    private $allowedOrigins = [];

    public function __construct($config = [])
    {
        if (isset($config['baseUri'])) {
            $this->baseUri = $config['baseUri'];
        }

        if (isset($config['allowedOrigins'])) {
            $this->allowedOrigins = $config['allowedOrigins'];
        }
    }

    public function registerRoutesFromController($controller)
    {
        try {
            $reflection = new ReflectionClass($controller);
        } catch (\ReflectionException $e) {
            // echo  'ReflectionException: ' . $e->getMessage() . "\n";
            die('ReflectionException: ' . $e->getMessage());
        }

        $routeGroupAttribute = $reflection->getAttributes(RouteGroup::class);
        if (!empty($routeGroupAttribute)) {
            $instance = $routeGroupAttribute[0]->newInstance();
            $previousPrefix = $this->prefix;
            $this->prefix .= $instance->prefix;
        }

        $classMiddlewares = $this->getClassMiddlewares($reflection);

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
                $httpMethod = $this->getHttpMethodFromAttribute($attribute->getName());
                if ($httpMethod) {
                    $routePath = $this->prefix . $instance->path;
                    $methodMiddlewares = $this->getMiddlewares($method);
                    $middlewares = array_merge($classMiddlewares, $methodMiddlewares);
                    $this->addRoute($httpMethod, $routePath, $controller, $method->getName(), $middlewares);
                    // echo  "Added route: $httpMethod $routePath -> $controller::" . $method->getName() . "\n";
                }
            }
        }

        if (!empty($routeGroupAttribute)) {
            $this->prefix = $previousPrefix;
        }
    }

    private function getHttpMethodFromAttribute($attributeName)
    {
        switch ($attributeName) {
            case Get::class:
                return 'GET';
            case Post::class:
                return 'POST';
            case Put::class:
                return 'PUT';
            case Delete::class:
                return 'DELETE';
            case Patch::class:
                return 'PATCH';
            case Options::class:
                return 'OPTIONS';
            case Head::class:
                return 'HEAD';
            case Trace::class:
                return 'TRACE';
            case Connect::class:
                return 'CONNECT';
            case Pri::class:
                return 'PRI';
            default:
                return null;
        }
    }

    private function getClassMiddlewares(ReflectionClass $class)
    {
        $middlewares = [];
        foreach ($class->getAttributes(Middleware::class) as $attribute) {
            $instance = $attribute->newInstance();
            $middlewares[] = $instance->middleware;
        }
        return $middlewares;
    }

    private function getMiddlewares(ReflectionMethod $method)
    {
        $middlewares = [];
        foreach ($method->getAttributes(Middleware::class) as $attribute) {
            $instance = $attribute->newInstance();
            $middlewares[] = $instance->middleware;
        }
        return $middlewares;
    }

    private function addRoute($method, $path, $resource, $resourceMethod, $middlewares = [])
    {
        $fullPath = '/' . trim($path, '/');

        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][$fullPath] = ['resource' => $resource, 'method' => $resourceMethod, 'middlewares' => $middlewares];
        // echo  "Route added: $method $fullPath\n";
    }

    public function dispatch(Request $request, Response $response)
    {
        try {
            $uri = $request->getUri();
            $parsedUri = parse_url($uri, PHP_URL_PATH);
            if ($this->baseUri && strpos($parsedUri, $this->baseUri) === 0) {
                $parsedUri = substr($parsedUri, strlen($this->baseUri));
            }

            if (!empty($this->allowedOrigins)) {
                $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
                $requestMethod = $request->getMethod();
                
                if ($this->isOriginAllowed($origin, $requestMethod)) {
                    header('Access-Control-Allow-Origin: ' . $origin);
                    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, TRACE, CONNECT, PRI');
                    header('Access-Control-Allow-Headers: Content-Type, Authorization');
                } else {
                    $response->withStatus(403)->write(json_encode(['error' => 'Forbidden', 'message' => 'Origin or method not allowed']));
                    return $response;
                }
            }

            // echo  "Parsed URI: " . $parsedUri . "\n";
            // echo  "Routes: \n";
            // var_dump($this->routes);
            if (!$this->handleRouting($parsedUri, $request, $response)) {
                return $response->withStatus(404)->withJSON(['error' => 'Not Found', 'message' => 'No route matches the provided URI']);
            }
        } catch (\Exception $exception) {
            $response->withStatus(500)->withJSON(['error' => 'Internal Server Error', 'message' => $exception->getMessage()]);
            return $response;
        }
        return $response;
    }

    private function isOriginAllowed($origin, $method)
    {
        foreach ($this->allowedOrigins as $allowedOrigin => $methods) {
            if (($allowedOrigin === '*' || $allowedOrigin === $origin) && in_array($method, $methods)) {
                return true;
            }
        }
        return false;
    }

    private function handleRouting($uri, Request $request, Response $response)
    {
        $requestMethod = $request->getMethod();
        // echo  "Handling routing for URI: $uri with method: $requestMethod\n";
        $matched = false;

        if (isset($this->routes[$requestMethod])) {
            foreach ($this->routes[$requestMethod] as $path => $details) {
                // echo  "Checking route: $path\n";
                $pattern = "@^" . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $path) . "$@D";
                // echo  "Pattern: " . $pattern . "\n";
                if (preg_match($pattern, $uri, $matches)) {
                    // echo  "Match found: \n";
                    // print_r($matches);
                    array_shift($matches);
                    
                    // Middleware kontrolü
                    $middlewares = $details['middlewares'];
                    $middlewareHandler = $this->createMiddlewareHandler($middlewares, function($req, $res) use ($details, $matches) {
                        $controllerName = $details['resource'];
                        $methodName = $details['method'];
                        $controller = new $controllerName;
                        return call_user_func_array([$controller, $methodName], array_merge([$req, $res], $matches));
                    });

                    return $middlewareHandler($request, $response);
                }
            }
        }

        if (!$matched && isset($this->routes[$requestMethod][$uri])) {
            $controllerName = $this->routes[$requestMethod][$uri]['resource'];
            $methodName = $this->routes[$requestMethod][$uri]['method'];
            $controller = new $controllerName;
            $controller->$methodName($request, $response);
            return true;
        }

        echo "No route matched for URI: $uri\n";
        return false;
    }

    private function createMiddlewareHandler(array $middlewares, callable $coreHandler)
    {
        $handler = array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function ($request, $response) use ($next, $middleware) {
                    $middlewareInstance = new $middleware;
                    return $middlewareInstance->handle($request, $response, $next);
                };
            },
            $coreHandler
        );

        return $handler;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
