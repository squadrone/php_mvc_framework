<?php

namespace App\Framework\Routing;

class Request
{
    private string $method = 'GET';
    private array $queryParameters;
    private array $postParameters;
    private ?string $contentType = null;
    private array $files;
    private array $segments;

    public function __construct()
    {
        $this->buildRequestFromServer($_SERVER);
    }

    private function buildRequestFromServer(array $server): void {
        $this->setMethod($server);
        $this->setQueryParameters($server);
        $this->setPostParameters($server);
        $this->setSegments($server);
        dump($this);
    }

    private function setMethod(array $server): void {
        if (array_key_exists('REQUEST_METHOD', $server)) {
            $this->method = strtoupper($server['REQUEST_METHOD']);
        }
    }

    private function setQueryParameters(array $server): void {
        $this->queryParameters = [];
        if (array_key_exists('QUERY_STRING', $server)) {
            if (!empty($server['QUERY_STRING'])) {
                $queryString = str_replace('?', '&', $server['QUERY_STRING']);
                $queryParts = explode('&', $queryString);
                foreach ($queryParts as $queryPart) {
                    $keyValues = explode('=', $queryPart);
                    if (count($keyValues) == 2) {
                        $this->queryParameters[$keyValues[0]] = trim(urldecode($keyValues[1]));
                    }
                }
            }
        }
    }

    private function setPostParameters(array $server): void {
        $this->postParameters = [];
        if ($this->method == 'POST') {
            if (array_key_exists('CONTENT_TYPE', $server)) {
                $this->contentType = $server['CONTENT_TYPE'];
                $this->setFiles();
            }
            if (isset($_POST) && is_array($_POST)) {
                foreach ($_POST as $key => $value) {
                    $this->postParameters[$key] = $value;
                }
            }
        }
    }

    private function setFiles(): void
    {
        $contentTypeParts = explode(';', $this->contentType);
        if (count($contentTypeParts) == 2 && $contentTypeParts[0] == 'multipart/form-data' && isset($_FILES) && is_array($_FILES)) {
            $this->files = $_FILES;
        }
    }

    private function setSegments(array $server): void {
        $this->segments = [];
        if (array_key_exists('REQUEST_URI', $server)) {
            $urlParts = explode('?', $server['REQUEST_URI']);
            if (count($urlParts) > 0) {
                $uri = $urlParts[0];
                if (array_key_exists('SCRIPT_NAME', $server)) {
                    if (str_starts_with($uri, $server['SCRIPT_NAME'])) {
                        $uri = preg_replace('/^' . preg_quote($server['SCRIPT_NAME'], '/') . '/', '', $uri);
                    }
                }
                $this->segments = array_values(array_filter(explode('/', $uri)));
            }
        }
    }

    public function get(string $key, string $default = ''): string {
        if (array_key_exists($key, $this->queryParameters)) {
            return $this->queryParameters[$key];
        }
        return $default;
    }

    public function post(string $key, string $default = ''): string {
        if (array_key_exists($key, $this->postParameters)) {
            return $this->postParameters[$key];
        }
        return $default;
    }

    public function file(string $key): array|null {
        if (array_key_exists($key, $this->files)) {
            return $this->files[$key];
        }
        return null;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getSegments(): array
    {
        return $this->segments;
    }
}