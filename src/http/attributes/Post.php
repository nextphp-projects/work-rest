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
 * Class Post
 *
 * A simple implementation of a PSR-7 HTTP message interface and PSR-12 extended coding style guide.
 * This class represents the POST HTTP method attribute.
 *
 * @package NextPHP\Rest\Http\Attributes
 */
class Post
{
    /**
     * @var string The path associated with the POST attribute.
     */
    public string $path;

    /**
     * Post constructor.
     *
     * @param string $path The path for the POST attribute.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
}