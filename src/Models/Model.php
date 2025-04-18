<?php

namespace LKDev\HetznerCloud\Models;

use GuzzleHttp\Client;
use LKDev\HetznerCloud\Clients\GuzzleClient;
use LKDev\HetznerCloud\HetznerAPIClient;
use stdClass;

abstract class Model
{

    public function __construct(protected Client|GuzzleClient|null $httpClient = null)
    {
        if ($this->httpClient === null) {
            $this->httpClient = HetznerAPIClient::$instance->getHttpClient();
        }
    }

    public function setHttpClient(GuzzleClient|Client|null $httpClient = null): void
    {
        $this->httpClient = $httpClient;
    }

    public static function parse(null|array|stdClass $input): null|static|array
    {
        return null;
    }
}
