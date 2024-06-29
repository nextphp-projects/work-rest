<?php

namespace NextPHP\App\Resource;

use NextPHP\Rest\Http\Request;
use NextPHP\Rest\Http\Response;
use NextPHP\Rest\Http\Attributes\Get;
use NextPHP\Rest\Http\Attributes\Post;
use NextPHP\Rest\Http\Attributes\RouteGroup;

#[RouteGroup('/test')]
class TestResource
{
    #[Get('/users')]
    public function getAllUsers(Request $request, Response $response)
    {
        $users = [
            [
                "id" => 1,
                "firstname" => "Vedat",
                "lastname" => "Yıldırım",
                "email" => "vedat@Nextphp.com",
                "role" => "admin",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ],
            [
                "id" => 2,
                "firstname" => "Jane",
                "lastname" => "Doe",
                "email" => "jane.doe@Nextphp.com",
                "role" => "user",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ]
        ];

        return $response->withJSON(['users' => $users]);
    }

    #[Get('/users/xml')]
    public function getUsersAsXML(Request $request, Response $response)
    {
        $users = [
            [
                "id" => 1,
                "firstname" => "Vedat",
                "lastname" => "Yıldırım",
                "email" => "vedat@Nextphp.com",
                "role" => "admin",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ],
            [
                "id" => 2,
                "firstname" => "Jane",
                "lastname" => "Doe",
                "email" => "jane.doe@Nextphp.com",
                "role" => "user",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ]
        ];

        return $response->withXML(['users' => $users]);
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

        return $response->withJSON(['user' => $newUser]);
    }

    #[Get('/users/html')]
    public function getUsersAsHTML(Request $request, Response $response)
    {
        $htmlContent = "<html><body><h1>Users</h1><ul><li>Vedat Yıldırım</li><li>Jane Doe</li></ul></body></html>";
        return $response->withHTML($htmlContent);
    }
    
    #[Get('/users/text')]
    public function getUsersAsTXT(Request $request, Response $response)
    {
        $users = [
            [
                "id" => 1,
                "firstname" => "Vedat",
                "lastname" => "Yıldırım",
                "email" => "vedat@Nextphp.com",
                "role" => "admin",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ],
            [
                "id" => 2,
                "firstname" => "Jane",
                "lastname" => "Doe",
                "email" => "jane.doe@Nextphp.com",
                "role" => "user",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ]
        ];

        return $response->withTEXT(['users' => $users]);
    }

    #[Get('/users/yaml')]
    public function getUsersAsYAML(Request $request, Response $response)
    {
        $users = [
            [
                "id" => 1,
                "firstname" => "Vedat",
                "lastname" => "Yıldırım",
                "email" => "vedat@Nextphp.com",
                "role" => "admin",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ],
            [
                "id" => 2,
                "firstname" => "Jane",
                "lastname" => "Doe",
                "email" => "jane.doe@Nextphp.com",
                "role" => "user",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ]
        ];

        return $response->withYAML(['users' => $users]);
    }

    #[Get('/users/csv')]
    public function getUsersAsCSV(Request $request, Response $response)
    {
        $users = [
            [
                "id" => 1,
                "firstname" => "Vedat",
                "lastname" => "Yıldırım",
                "email" => "vedat@Nextphp.com",
                "role" => "admin",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ],
            [
                "id" => 2,
                "firstname" => "Jane",
                "lastname" => "Doe",
                "email" => "jane.doe@Nextphp.com",
                "role" => "user",
                "created_at" => "2024-06-29 14:34:21",
                "updated_at" => "2024-06-29 14:34:21"
            ]
        ];

        return $response->withCSV($users);
    }

    #[Get('/users/binary')]
    public function getUsersAsBinary(Request $request, Response $response)
    {
        $binaryData = "Example binary data for users.";
        return $response->withBinary($binaryData);
    }
}