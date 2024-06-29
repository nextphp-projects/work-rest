<?php

namespace NextPHP\Rest\Http;

class Request
{
    private $method;
    private $uri;
    private $headers;
    private $body;
    private $queryParams;
    private $parsedBody;

    public function __construct($method, $uri, $headers, $body, $queryParams, $parsedBody)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }
}
