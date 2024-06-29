<?php

namespace NextPHP\Rest\Http\Attributes;

#[\Attribute]
class RouteGroup
{
    public string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }
}