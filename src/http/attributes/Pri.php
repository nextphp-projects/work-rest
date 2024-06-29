<?php

namespace NextPHP\Rest\Http\Attributes;

#[\Attribute]
class Pri
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
