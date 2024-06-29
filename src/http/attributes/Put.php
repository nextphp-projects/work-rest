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
 * Class Put
 *
 * A simple implementation of a PSR-7 HTTP message interface and PSR-12 extended coding style guide.
 * This class represents the PUT HTTP method attribute.
 *
 * @package NextPHP\Rest\Http\Attributes
 */
class Put
{
    /**
     * @var string The path associated with the PUT attribute.
     */
    public string $path;

    /**
     * Put constructor.
     *
     * @param string $path The path for the PUT attribute.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
}