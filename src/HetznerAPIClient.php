<?php

namespace LKDev\HetznerCloud;

use GuzzleHttp\Client;
use LKDev\HetznerCloud\Clients\GuzzleClient;
use LKDev\HetznerCloud\Models\Actions\Actions;
use LKDev\HetznerCloud\Models\Certificates\Certificates;
use LKDev\HetznerCloud\Models\Datacenters\Datacenters;
use LKDev\HetznerCloud\Models\Firewalls\Firewalls;
use LKDev\HetznerCloud\Models\FloatingIps\FloatingIps;
use LKDev\HetznerCloud\Models\Images\Images;
use LKDev\HetznerCloud\Models\ISOs\ISOs;
use LKDev\HetznerCloud\Models\LoadBalancers\LoadBalancers;
use LKDev\HetznerCloud\Models\LoadBalancerTypes\LoadBalancerTypes;
use LKDev\HetznerCloud\Models\Networks\Networks;
use LKDev\HetznerCloud\Models\PlacementGroups\PlacementGroups;
use LKDev\HetznerCloud\Models\Prices\Prices;
use LKDev\HetznerCloud\Models\PrimaryIps\PrimaryIps;
use LKDev\HetznerCloud\Models\Servers\Servers;
use LKDev\HetznerCloud\Models\Servers\Types\ServerTypes;
use LKDev\HetznerCloud\Models\SSHKeys\SSHKeys;
use LKDev\HetznerCloud\Models\Volumes\Volumes;
use Psr\Http\Message\ResponseInterface;

class HetznerAPIClient
{
    const string VERSION = '2.8.0';
    const int MAX_ENTITIES_PER_PAGE = 50;

    /**
     * The default instance of the HTTP client, for easily getting it in the child models.
     */
    public static HetznerAPIClient $instance;

    protected GuzzleClient|Client $httpClient;

    public function __construct(
        public string $api_token,
        public string $base_url = 'https://api.hetzner.cloud/v1/',
        public string $user_agent = ''
    )
    {
        $this->httpClient = new GuzzleClient($this);
        self::$instance = $this;
    }

    public function getUserAgent(): string
    {
        return $this->user_agent;
    }

    public function getApiToken(): string
    {
        return $this->api_token;
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    public function setUserAgent(string $user_agent): self
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    public function setBaseUrl(string $base_url): self
    {
        $this->base_url = $base_url;

        return $this;
    }

    public function getHttpClient(): GuzzleClient|Client
    {
        return $this->httpClient;
    }

    public function setHttpClient(GuzzleClient|Client $client): self
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * @throws APIException
     */
    public static function throwError(ResponseInterface $response)
    {
        $body = (string)$response->getBody();
        if (strlen($body) > 0) {
            $error = json_decode($body);
            throw new APIException(APIResponse::create([
                'error' => $error->error,
            ]), $error->error->message);
        }
        throw new APIException(APIResponse::create([
            'response' => $response,
        ]), 'The response is not parseable');
    }

    /**
     * @throws APIException
     */
    public static function hasError(ResponseInterface $response): bool
    {
        $responseDecoded = json_decode((string)$response->getBody());
        if (strlen((string)$response->getBody()) > 0) {
            if (property_exists($responseDecoded, 'error')) {
                self::throwError($response);
            }
        } elseif ($response->getStatusCode() <= 200 && $response->getStatusCode() >= 300) {
            self::throwError($response);
        }

        return false;
    }

    public function actions(): Actions
    {
        return new Actions($this->httpClient);
    }

    public function servers(): Servers
    {
        return new Servers($this->httpClient);
    }

    public function volumes(): Volumes
    {
        return new Volumes($this->httpClient);
    }

    public function serverTypes(): ServerTypes
    {
        return new ServerTypes($this->httpClient);
    }

    public function datacenters(): Datacenters
    {
        return new Datacenters($this->httpClient);
    }

    public function locations(): Models\Locations\Locations
    {
        return new Models\Locations\Locations($this->httpClient);
    }

    public function images(): Images
    {
        return new Images($this->httpClient);
    }

    public function sshKeys(): SSHKeys
    {
        return new SSHKeys($this->httpClient);
    }

    public function prices(): Prices
    {
        return new Prices($this->httpClient);
    }

    public function isos(): ISOs
    {
        return new ISOs($this->httpClient);
    }

    public function floatingIps(): FloatingIps
    {
        return new FloatingIps($this->httpClient);
    }

    public function primaryIps(): PrimaryIps
    {
        return new PrimaryIps($this->httpClient);
    }

    public function networks(): Networks
    {
        return new Networks($this->httpClient);
    }

    public function placementGroups(): PlacementGroups
    {
        return new PlacementGroups($this->httpClient);
    }

    public function certificates(): Certificates
    {
        return new Certificates($this->httpClient);
    }

    public function firewalls(): Firewalls
    {
        return new Firewalls($this->httpClient);
    }

    public function loadBalancers(): LoadBalancers
    {
        return new LoadBalancers($this->httpClient);
    }

    public function loadBalancerTypes(): LoadBalancerTypes
    {
        return new LoadBalancerTypes($this->httpClient);
    }

    public function httpClient(): GuzzleClient
    {
        return $this->httpClient;
    }
}
