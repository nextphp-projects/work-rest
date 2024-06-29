<?php

namespace NextPHP\Rest\Http\Attributes;

#[\Attribute]
class Get
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
