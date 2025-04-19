<?php

namespace LKDev\HetznerCloud\Models\Networks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Protection;
use LKDev\HetznerCloud\Models\Servers\Server;
use LKDev\HetznerCloud\Models\Servers\ServerReference;

/**
 * Class Network.
 */
class Network extends NetworkReference implements Resource
{
    public ?string $name = null;
    public ?string $ip_range = null;
    public array $subnets = [];
    public array $routes = [];
    /** @var ServerReference[]  */
    public array $servers = [];
    public Protection|array|null $protection = null;
    public array $labels = [];
    public ?string $created = null;

    public function __construct(
        int $id,
        ?string $name = null,
        ?Client    $httpClient = null)
    {
        parent::__construct(id: $id, name: $name, httpClient: $httpClient);
    }

    /**
     * @throws APIException|GuzzleException
     * @see https://docs.hetzner.cloud/#network-actions-add-a-subnet-to-a-network
     *
     */
    public function addSubnet(Subnet $subnet): ?APIResponse
    {
        $response = $this->httpClient->post('networks/' . $this->id . '/actions/add_subnet', [
            'json' => $subnet->__toRequestPayload(),
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws APIException|GuzzleException
     * @see https://docs.hetzner.cloud/#network-actions-delete-a-subnet-from-a-network
     *
     */
    public function deleteSubnet(Subnet $subnet): ?APIResponse
    {
        $response = $this->httpClient->post('networks/' . $this->id . '/actions/delete_subnet', [
            'json' => ['ip_range' => $subnet->ipRange],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws APIException|GuzzleException
     * @see https://docs.hetzner.cloud/#network-actions-add-a-route-to-a-network
     */
    public function addRoute(Route $route): ?APIResponse
    {
        $response = $this->httpClient->post('networks/' . $this->id . '/actions/add_route', [
            'json' => $route->__toRequestPayload(),
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws APIException|GuzzleException
     * @see https://docs.hetzner.cloud/#network-actions-delete-a-route-from-a-network
     */
    public function deleteRoute(Route $route): ?APIResponse
    {
        $response = $this->httpClient->post('networks/' . $this->id . '/actions/delete_route', [
            'json' => $route->__toRequestPayload(),
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws APIException|GuzzleException
     * @see https://docs.hetzner.cloud/#network-actions-change-ip-range-of-a-network
     */
    public function changeIPRange(string $ipRange): ?APIResponse
    {
        $response = $this->httpClient->post('networks/' . $this->id . '/actions/change_ip_range', [
            'json' => ['ip_range' => $ipRange],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Changes the protection configuration of the network.
     * @see https://docs.hetzner.cloud/#network-actions-change-network-protection
     * @throws APIException|GuzzleException
     */
    public function changeProtection(bool $delete = true): ?APIResponse
    {
        $response = $this->httpClient->post('networks/' . $this->id . '/actions/change_protection', [
            'json' => [
                'delete' => $delete,
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    private function setAdditionalData($data): static
    {
        $this->name = $data->name;
        $this->ip_range = $data->ip_range;
        $this->subnets = Subnet::parse($data->subnets, $this->httpClient);
        $this->routes = Route::parse($data->routes, $this->httpClient);
        $this->servers = collect($data->servers)
            ->map(function ($id) {
                return new ServerReference($id);
            })->toArray();
        $this->protection = Protection::parse($data->protection);

        $this->labels = get_object_vars($data->labels);
        $this->created = $data->created;

        return $this;
    }

    public static function parse($input): null|static
    {
        return (new self(id: $input->id))->setAdditionalData($input);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->networks()->get($this->id);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('networks/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }
        return null;
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function update(array $data): ?APIResponse
    {
        $response = $this->httpClient->put('networks/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'network' => Server::parse(json_decode((string)$response->getBody())->network),
            ], $response->getHeaders());
        }
        return null;
    }
}
