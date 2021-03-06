<?php declare(strict_types=1);
namespace LightApp\Model\System;

class Request
{
    protected const DEFAULT_INPUT_TYPE = 'query';

    protected const INPUT_TYPE_QUERY = 'query';
    protected const INPUT_TYPE_JSON = 'json';

    private $host;
    private $path;
    private $route;
    private $attributes;
    private $method;
    private $query;
    private $payload;
    private $files;
    private $input;
    private $cookies;
    private $server;
    private $routedController;
    private $routedAction;
    private $defaultContentType;

    public function __construct(
        string $host,
        string $path,
        string $route,
        array $attributes,
        string $method,
        array $query,
        array $payload,
        array $files,
        string $input,
        array $cookies,
        array $server,
        string $routedController,
        string $routedAction,
        string $defaultContentType
    ) {
        $this->host = $host;
        $this->path = $path;
        $this->route = $route;
        $this->attributes = $attributes;
        $this->method = $method;
        $this->query = $query;
        $this->payload = $payload;
        $this->files = $files;
        $this->input = $input;
        $this->cookies = $cookies;
        $this->server = $server;
        $this->routedController = $routedController;
        $this->routedAction = $routedAction;
        $this->defaultContentType = $defaultContentType;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getRoute() : string
    {
        return $this->route;
    }

    public function getAttributes(array $combinedKeys = []) : array
    {
        return !empty($combinedKeys) ? $this->getFromArray($combinedKeys, $this->attributes) : $this->attributes;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function isAjax() : bool
    {
        if(isset($this->server['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' === strtolower($this->server['HTTP_X_REQUESTED_WITH'])) {
            return true;
        }

        return false;
    }

    public function getQuery(array $combinedKeys = []) : array
    {
        return !empty($combinedKeys) ? $this->getFromArray($combinedKeys, $this->query) : $this->query;
    }

    public function getPayload(array $combinedKeys = []) : array
    {
        return !empty($combinedKeys) ? $this->getFromArray($combinedKeys, $this->payload) : $this->payload;
    }

    public function getFiles(array $combinedKeys = []) : array
    {
        $files = [];
        foreach ($this->files as $inputName => $file) {
            if (!is_array($file['name'])) {
                $files[$inputName] = $file;
            } else {
                $keys = array_keys($file);
                foreach ($file['name'] as $index => $value) {
                    foreach($keys as $key) {
                        $content[$key] =$file[$key][$index];
                    }
                    $files[$inputName][] = $content;
                }

            }
        }

        return !empty($combinedKeys) ? $this->getFromArray($combinedKeys, $files) : $files;
    }

    public function getInput(array $combinedKeys = [], string $type = null) : array
    {
        $type = $type ?? static::DEFAULT_INPUT_TYPE;
        switch ($type) {
            case static::INPUT_TYPE_QUERY:
                parse_str($this->input, $input);
                break;
            case static::INPUT_TYPE_JSON:
                $input = json_decode($this->input, true);
                break;
            default:
                throw new \Exception('Not supported input type rule ' . $selectedRule);
        }
        return !empty($combinedKeys) ? $this->getFromArray($combinedKeys, $input) : $input;
    }

    public function getCookies(array $combinedKeys = []) : array
    {
        return !empty($combinedKeys) ? $this->getFromArray($combinedKeys, $this->cookies) : $this->cookies;
    }

    public function getServer(array $combinedKeys = []) : array
    {
        return !empty($combinedKeys) ? $this->getFromArray($combinedKeys, $this->server) : $this->server;
    }

    public function getServerProtocol() : string
    {
        return $this->server['SERVER_PROTOCOL'] ?? '';
    }

    public function getController() : string
    {
        return $this->routedController;
    }

    public function getAction() : string
    {
        return $this->routedAction;
    }

    public function getDefaultContentType() : string
    {
        return $this->defaultContentType;
    }

    private function getFromArray(array $combinedKeys, array $arrayToFilter) : array
    {
        $return = [];
        foreach ($combinedKeys as $combinedKey) {
            $nesting = explode('.', $combinedKey);
            $arrayChunk = $arrayToFilter;
            foreach ($nesting as $key) {
                if (isset($arrayChunk[$key])) {
                    $arrayChunk = $arrayChunk[$key];
                } else {
                    $arrayChunk = null;
                }
            }
            $return[$combinedKey] = $arrayChunk;
        }
        return $return;
    }
}
