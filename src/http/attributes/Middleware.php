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

namespace NextPHP\Rest\Http\Attributes;

#[\Attribute]
/**
 * Class Middleware
 *
 * A simple implementation of a PSR-7 HTTP message interface and PSR-12 extended coding style guide.
 * This class represents a middleware attribute.
 *
 * @package NextPHP\Rest\Http\Attributes
 */
class Middleware
{
    /**
     * @var string The middleware class name.
     */
    public string $middleware;

    /**
     * Middleware constructor.
     *
     * @param string $middleware The middleware class name.
     */
    public function __construct(string $middleware)
    {
        $this->middleware = $middleware;
    }
}