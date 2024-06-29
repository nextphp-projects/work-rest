<?php

namespace NextPHP\Rest\Http\Attributes;

#[\Attribute]
class Middleware
{
    public string $middleware;

    public function __construct(string $middleware)
    {
        $this->middleware = $middleware;
    }
}
