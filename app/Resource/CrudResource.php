<?php

namespace NextPHP\App\Resource;

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
use NextPHP\Rest\Http\Middleware\AuthMiddleware;

#[RouteGroup('/api')]
// #[Middleware(AuthMiddleware::class)]
class CrudResource
{
    private $users = [];

    public function __construct()
    {
        $this->users = [
            1 => [
                "id" => 1,
                "firstname" => "Vedat",
                "lastname" => "Yıldırım",
                "email" => "vedat@nextphp.com",
                "role" => "admin",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ],
            2 => [
                "id" => 2,
                "firstname" => "Jane",
                "lastname" => "Doe",
                "email" => "jane.doe@nextphp.com",
                "role" => "user",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ]
        ];
    }

    #[Get('/users')]
    #[Middleware(AuthMiddleware::class)]
    public function getAllUsers(Request $request, Response $response)
    {
        return $response->withJSON(['users' => array_values($this->users)]);
    }

    #[Get('/users/{id}')]
    #[Middleware(AuthMiddleware::class)]
    public function getUserById(Request $request, Response $response, $id)
    {
        $userId = (int) $id;
        if (isset($this->users[$userId])) {
            return $response->withJSON($this->users[$userId]);
        } else {
            return $response->withStatus(404)->withJSON(['error' => 'User not found']);
        }
    }

    #[Post('/users')]
    public function createUser(Request $request, Response $response)
    {
        $data = json_decode($request->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response->withStatus(400)->withJSON(['error' => 'Invalid JSON provided']);
        }
        
        $newUser = [
            "id" => rand(3, 1000),
            "firstname" => $data['firstname'] ?? '',
            "lastname" => $data['lastname'] ?? '',
            "email" => $data['email'] ?? '',
            "role" => $data['role'] ?? '',
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s')
        ];

        $this->users[$newUser['id']] = $newUser;
        return $response->withStatus(201)->withJSON($newUser);
    }

    #[Put('/users/{id}')]
    public function updateUser(Request $request, Response $response, $id)
    {
        $userId = (int) $id;
        if (!isset($this->users[$userId])) {
            return $response->withStatus(404)->withJSON(['error' => 'User not found']);
        }

        $data = json_decode($request->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response->withStatus(400)->withJSON(['error' => 'Invalid JSON provided']);
        }

        $this->users[$userId] = array_merge($this->users[$userId], $data);
        $this->users[$userId]['updated_at'] = date('Y-m-d H:i:s');

        return $response->withJSON($this->users[$userId]);
    }

    #[Patch('/users/{id}')]
    public function patchUser(Request $request, Response $response, $id)
    {
        $userId = (int) $id;
        if (!isset($this->users[$userId])) {
            return $response->withStatus(404)->withJSON(['error' => 'User not found']);
        }

        $data = json_decode($request->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response->withStatus(400)->withJSON(['error' => 'Invalid JSON provided']);
        }

        $this->users[$userId] = array_merge($this->users[$userId], $data);
        $this->users[$userId]['updated_at'] = date('Y-m-d H:i:s');

        return $response->withJSON($this->users[$userId]);
    }

    #[Delete('/users/{id}')]
    public function deleteUser(Request $request, Response $response, $id)
    {
        $userId = (int) $id;
        if (!isset($this->users[$userId])) {
            return $response->withStatus(404)->withJSON(['error' => 'User not found']);
        }

        // unset($this->users[$userId]);
        $this->users[$userId]['deleted_at'] = date('Y-m-d H:i:s');

        // return $response->withStatus(204);
        return $response->withJSON($this->users[$userId]);
    }

    #[Options('/users')]
    public function optionsUsers(Request $request, Response $response)
    {
        return $response->withHeader('Allow', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, TRACE, CONNECT, PRI');
    }

    #[Head('/users')]
    public function headUsers(Request $request, Response $response)
    {
        return $response->withStatus(200);
    }

    #[Trace('/users')]
    public function traceUsers(Request $request, Response $response)
    {
        $body = $request->getBody();
        return $response->withText($body);
    }

    #[Connect('/users')]
    public function connectUsers(Request $request, Response $response)
    {
        return $response->withStatus(200)->withText('CONNECT method processed');
    }

    #[Pri('/users')]
    public function priUsers(Request $request, Response $response)
    {
        return $response->withStatus(200)->withText('PRI method processed');
    }
}