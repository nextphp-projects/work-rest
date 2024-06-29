<?php

namespace NextPHP\Rest\Http;

class Response
{
    private $headers = [];
    private $statusCode = 200;
    private $body;

    public function write($data)
    {
        $this->body = $data;
        return $this;
    }

    public function withJSON($data)
    {
        $this->body = json_encode($data);
        $this->withHeader('Content-Type', 'application/json');
        return $this;
    }

    public function withXML($data)
    {
        $this->body = $this->toXML($data);
        $this->withHeader('Content-Type', 'application/xml');
        return $this;
    }

    public function withTEXT($data)
    {
        if (is_array($data) || is_object($data)) {
            $this->body = print_r($data, true); // Dizi veya nesneyi metne dönüştür
        } else {
            $this->body = (string)$data; // Diğer veri tiplerini metne dönüştür
        }
        $this->withHeader('Content-Type', 'text/plain');
        return $this;
    }

    public function withHTML($data)
    {
        $this->body = (string)$data;
        $this->withHeader('Content-Type', 'text/html');
        return $this;
    }

    public function withYAML($data)
    {
        if (function_exists('yaml_emit')) {
            $this->body = yaml_emit($data);
            $this->withHeader('Content-Type', 'application/x-yaml');
        } else {
            $this->body = "YAML extension is not available.";
            $this->withHeader('Content-Type', 'text/plain');
        }
        return $this;
    }

    public function withCSV($data)
    {
        $csv = $this->toCSV($data);
        if ($csv !== false) {
            $this->body = $csv;
            $this->withHeader('Content-Type', 'text/csv');
        } else {
            $this->body = "Error converting data to CSV.";
            $this->withHeader('Content-Type', 'text/plain');
        }
        return $this;
    }

    public function withBinary($data)
    {
        $this->body = $data;
        $this->withHeader('Content-Type', 'application/octet-stream');
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function withStatus($code)
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    private function toXML($data, $rootElement = 'root', $xml = null)
    {
        if ($xml === null) {
            $xml = new \SimpleXMLElement("<$rootElement/>");
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->toXML($value, $key, $xml->addChild($key));
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }

    private function toCSV($data)
    {
        if (empty($data)) {
            return false;
        }

        ob_start();
        $df = fopen("php://output", 'w');
        if ($df === false) {
            return false;
        }

        fputcsv($df, array_keys(reset($data)));
        foreach ($data as $row) {
            fputcsv($df, $row);
        }
        fclose($df);

        return ob_get_clean();
    }
}