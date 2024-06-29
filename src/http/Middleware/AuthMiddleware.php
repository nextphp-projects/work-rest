<?php

namespace NextPHP\Rest\Http\Middleware;

use NextPHP\Rest\Http\Request;
use NextPHP\Rest\Http\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{

    public function handle2()
    {
        $authHeader = $request->getHeaders()['Authorization'] ?? '';
        if (!$authHeader) {
            return $response->withStatus(401)->withJSON(['error' => 'Unauthorized']);
        }

        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if (!$jwt) {
            return $response->withStatus(401)->withJSON(['error' => 'Unauthorized']);
        }

        try {
            $decoded = JWT::decode($jwt, new Key('your-secret-key', 'HS256'));
            // Token geçerli, isteğe devam et
            return $next($request, $response);
        } catch (\Exception $e) {
            return $response->withStatus(401)->withJSON(['error' => 'Unauthorized']);
        }
    }
    
    public function handle(Request $request, Response $response, callable $next)
    {
        $authHeader = $request->getHeaders()['Authorization'] ?? '';
        if (!$authHeader) {
            return $response->withStatus(401)->withJSON(['error' => 'Unauthorized']);
        }

        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if (!$jwt) {
            return $response->withStatus(401)->withJSON(['error' => 'Unauthorized']);
        }

        try {
            $decoded = JWT::decode($jwt, new Key('your-secret-key', 'HS256'));
            // Token geçerli, isteğe devam et
            return $next($request, $response);
        } catch (\Exception $e) {
            return $response->withStatus(401)->withJSON(['error' => 'Unauthorized']);
        }
    }
}