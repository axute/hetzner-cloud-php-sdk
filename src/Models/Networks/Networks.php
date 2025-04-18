<?php

namespace LKDev\HetznerCloud\Models\Networks;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

/**
 * Class Networks.
 */
class Networks extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $networks = [];

    /**
     * Returns all existing server objects.
     * @see https://docs.hetzner.cloud/#networks-get-all-networks
     * @throws GuzzleException|APIException
     *
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new NetworkRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns a specific server object. The server must exist inside the project.
     * @see https://docs.hetzner.cloud/#networks-get-a-network
     * @throws APIException
     * @throws GuzzleException
     */
    public function getById(int $id): ?Network
    {
        $response = $this->httpClient->get('networks/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return Network::parse(json_decode((string)$response->getBody())->network);
        }

        return null;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function setAdditionalData($input): static
    {
        $this->networks = collect($input)->map(function ($network) {
            if ($network != null) {
                return Network::parse($network);
            }
            return null;
        })->toArray();

        return $this;
    }

    /**
     * Returns a specific network object by its name. The network must exist inside the project.
     * @see https://docs.hetzner.cloud/#networks-get-all-networks
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?Network
    {
        /** @var Networks $networks */
        $networks = $this->list(new NetworkRequestOpts($name));

        return (count($networks->networks) > 0) ? $networks->networks[0] : null;
    }

    /**
     * Returns all existing server objects.
     * @see https://docs.hetzner.cloud/#networks-get-all-networks
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new NetworkRequestOpts();
        }
        $response = $this->httpClient->get('networks' . $requestOpts->buildQuery());
        if (!HetznerAPIClient::hasError($response)) {
            $resp = json_decode((string)$response->getBody());

            return APIResponse::create([
                'meta'                    => Meta::parse($resp->meta),
                $this->_getKeys()['many'] => self::parse($resp->{$this->_getKeys()['many']})->{$this->_getKeys()['many']},
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function create(string $name, string $ipRange, array $subnets = [], array $routes = [], array $labels = []): ?APIResponse
    {
        $payload = [
            'name'     => $name,
            'ip_range' => $ipRange,
        ];
        if (!empty($subnets)) {
            $payload['subnets'] = collect($subnets)->map(function (Subnet $s) {
                return $s->__toRequestPayload();
            })->toArray();
        }
        if (!empty($routes)) {
            $payload['routes'] = collect($routes)->map(function (Route $r) {
                return $r->__toRequestPayload();
            })->toArray();
        }
        if (!empty($labels)) {
            $payload['labels'] = $labels;
        }

        $response = $this->httpClient->post('networks', [
            'json' => $payload,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'network' => Network::parse($payload->network),
            ], $response->getHeaders());
        }
        return null;
    }

    public function _getKeys(): array
    {
        return [
            'one'  => 'network',
            'many' => 'networks'
        ];
    }
}
