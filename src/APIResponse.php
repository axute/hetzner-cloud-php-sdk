<?php

/**
 * Created by PhpStorm.
 * User: lkaemmerling
 * Date: 08.08.18
 * Time: 14:06.
 */

namespace LKDev\HetznerCloud;

use LKDev\HetznerCloud\Models\Model;
use stdClass;

class APIResponse
{
    protected array $header = [];
    protected array $response = [];

    public function getResponse(): array
    {
        return $this->response;
    }

    public function getResponsePart(?string $resource = null): Model|stdClass|bool|string|array
    {
        return (array_key_exists($resource, $this->response)) ? $this->response[$resource] : false;
    }

    public function setResponse(array $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function setHeader(array $header): static
    {
        $this->header = $header;
        return $this;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public static function create(array $response, array $header = []): static
    {
        return (new self())->setResponse($response)->setHeader($header);
    }

    public function __get($name)
    {
        return $this->getResponsePart($name);
    }
}
