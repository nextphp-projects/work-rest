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

declare(strict_types=1);

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
use NextPHP\Rest\Logging\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class Router
 *
 * A simple implementation of a PSR-12 compatible coding style and a PSR-3 compatible logger.
 * 
 * This class is responsible for handling HTTP routing in the NextPHP REST framework.
 * It supports registering routes from controller classes using PHP 8 attributes,
 * dispatching incoming HTTP requests, and applying middleware.
 */
class Router
{
    /**
     * @var array The registered routes.
     */
    private array $routes = [];

    /**
     * @var string The current route prefix.
     */
    private string $prefix = '';

    /**
     * @var string The base URI for the router.
     */
    private string $baseUri = '';

    /**
     * @var array Allowed origins for CORS.
     */
    private array $allowedOrigins = [];

    /**
     * @var LoggerInterface The logger instance.
     */
    private LoggerInterface $logger;

    /**
     * Router constructor.
     *
     * @param array $config Configuration options.
     * @param LoggerInterface $logger Logger instance.
     */
    public function __construct(array $config = [], LoggerInterface $logger)
    {
        $this->logger = $logger;
        if (isset($config['baseUri'])) {
            $this->baseUri = $config['baseUri'];
        }

        if (isset($config['allowedOrigins'])) {
            $this->allowedOrigins = $config['allowedOrigins'];
        }
    }

    /**
     * Registers routes from the provided controller class.
     *
     * @param string $controller The controller class name.
     */
    public function registerRoutesFromController(string $controller): void
    {
        try {
            $reflection = new ReflectionClass($controller);
        } catch (\ReflectionException $e) {
            $this->logger->error('ReflectionException: ' . $e->getMessage());
            return;
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
                }
            }
        }

        if (!empty($routeGroupAttribute)) {
            $this->prefix = $previousPrefix;
        }
    }

    /**
     * Determines the HTTP method from the given attribute name.
     *
     * @param string $attributeName The attribute class name.
     * @return string|null The HTTP method or null if not matched.
     */
    private function getHttpMethodFromAttribute(string $attributeName): ?string
    {
        return match ($attributeName) {
            Get::class => 'GET',
            Post::class => 'POST',
            Put::class => 'PUT',
            Delete::class => 'DELETE',
            Patch::class => 'PATCH',
            Options::class => 'OPTIONS',
            Head::class => 'HEAD',
            Trace::class => 'TRACE',
            Connect::class => 'CONNECT',
            Pri::class => 'PRI',
            default => null,
        };
    }

    /**
     * Retrieves the middlewares defined on the given class.
     *
     * @param ReflectionClass $class The reflection class instance.
     * @return array The middlewares.
     */
    private function getClassMiddlewares(ReflectionClass $class): array
    {
        $middlewares = [];
        foreach ($class->getAttributes(Middleware::class) as $attribute) {
            $instance = $attribute->newInstance();
            $middlewares[] = $instance->middleware;
        }
        return $middlewares;
    }

    /**
     * Retrieves the middlewares defined on the given method.
     *
     * @param ReflectionMethod $method The reflection method instance.
     * @return array The middlewares.
     */
    private function getMiddlewares(ReflectionMethod $method): array
    {
        $middlewares = [];
        foreach ($method->getAttributes(Middleware::class) as $attribute) {
            $instance = $attribute->newInstance();
            $middlewares[] = $instance->middleware;
        }
        return $middlewares;
    }

    /**
     * Adds a route to the router.
     *
     * @param string $method The HTTP method.
     * @param string $path The route path.
     * @param string $resource The controller class name.
     * @param string $resourceMethod The controller method name.
     * @param array $middlewares The middlewares to apply.
     */
    private function addRoute(string $method, string $path, string $resource, string $resourceMethod, array $middlewares = []): void
    {
        $fullPath = '/' . trim($path, '/');

        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][$fullPath] = [
            'resource' => $resource,
            'method' => $resourceMethod,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Dispatches the incoming request to the appropriate route.
     *
     * @param Request $request The HTTP request.
     * @param Response $response The HTTP response.
     * @return Response The modified response.
     */
    public function dispatch(Request $request, Response $response): Response
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
                    return $response->withStatus(403)->withJSON([
                        'error' => 'Forbidden',
                        'message' => 'Origin or method not allowed'
                    ]);
                }
            }

            if (!$this->handleRouting($parsedUri, $request, $response)) {
                return $response->withStatus(404)->withJSON([
                    'error' => 'Not Found',
                    'message' => 'No route matches the provided URI'
                ]);
            }
        } catch (\Exception $exception) {
            $this->logger->error('Exception: ' . $exception->getMessage(), ['exception' => $exception]);
            return $response->withStatus(500)->withJSON([
                'error' => 'Internal Server Error',
                'message' => $exception->getMessage()
            ]);
        }
        return $response;
    }

    /**
     * Checks if the origin is allowed for CORS.
     *
     * @param string $origin The request origin.
     * @param string $method The HTTP method.
     * @return bool True if the origin is allowed, false otherwise.
     */
    private function isOriginAllowed(string $origin, string $method): bool
    {
        foreach ($this->allowedOrigins as $allowedOrigin => $methods) {
            if (($allowedOrigin === '*' || $allowedOrigin === $origin) && in_array($method, $methods, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handles the routing of the request.
     *
     * @param string $uri The request URI.
     * @param Request $request The HTTP request.
     * @param Response $response The HTTP response.
     * @return bool True if a route was matched, false otherwise.
     */
    private function handleRouting(string $uri, Request $request, Response $response): bool
    {
        $requestMethod = $request->getMethod();
        $matched = false;

        if (isset($this->routes[$requestMethod])) {
            foreach ($this->routes[$requestMethod] as $path => $details) {
                $pattern = "@^" . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $path) . "$@D";
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches);

                    $middlewares = $details['middlewares'];
                    $middlewareHandler = $this->createMiddlewareHandler($middlewares, function ($req, $res) use ($details, $matches) {
                        $controllerName = $details['resource'];
                        $methodName = $details['method'];
                        $controller = new $controllerName;
                        return call_user_func_array([$controller, $methodName], array_merge([$req, $res], $matches));
                    });

                    return (bool)$middlewareHandler($request, $response);
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

        return false;
    }

    /**
     * Creates a middleware handler that wraps the core handler.
     *
     * @param array $middlewares The middlewares to apply.
     * @param callable $coreHandler The core request handler.
     * @return callable The composed middleware handler.
     */
    private function createMiddlewareHandler(array $middlewares, callable $coreHandler): callable
    {
        return array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function ($request, $response) use ($next, $middleware) {
                    $middlewareInstance = new $middleware;
                    return $middlewareInstance->handle($request, $response, $next);
                };
            },
            $coreHandler
        );
    }

    /**
     * Gets the registered routes.
     *
     * @return array The registered routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}