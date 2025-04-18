<?php

namespace LKDev\HetznerCloud\Models;

use GuzzleHttp\Client;
use LKDev\HetznerCloud\Clients\GuzzleClient;
use LKDev\HetznerCloud\HetznerAPIClient;

abstract class Model
{
    protected GuzzleClient|Client $httpClient;

    /**
     * Model constructor.
     *
     * @param Client|GuzzleClient|null $httpClient
     */
    public function __construct(Client|GuzzleClient|null $httpClient = null)
    {
        if ($httpClient !== null) {
            $this->setHttpClient($httpClient);
        } else {
            $this->setHttpClient(HetznerAPIClient::$instance->getHttpClient());
        }
    }

    public function setHttpClient(GuzzleClient|Client|null $httpClient = null): void
    {
        $this->httpClient = $httpClient;
    }

    public static function parse($input): null|static
    {
        return null;
    }
}
