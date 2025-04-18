<?php

namespace LKDev\HetznerCloud\Models\LoadBalancerTypes;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class LoadBalancerTypes extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $load_balancer_types;

    /**
     * Returns all load balancer type objects.
     * @see https://docs.hetzner.cloud/#load-balancer-types-get-all-load-balancer-types
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new LoadBalancerTypeRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all load balancer type objects.
     * @see https://docs.hetzner.cloud/#load-balancer-types-get-all-load-balancer-types
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new LoadBalancerTypeRequestOpts();
        }
        $response = $this->httpClient->get('load_balancer_types' . $requestOpts->buildQuery());
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
     * Gets a specific Load Balancer type object.
     * @see https://docs.hetzner.cloud/#load-balancer-types-get-a-load-balancer-type
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?LoadBalancerType
    {
        $response = $this->httpClient->get('load_balancer_types/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return LoadBalancerType::parse(json_decode((string)$response->getBody())->load_balancer_type);
        }

        return null;
    }

    /**
     * Gets a specific Load Balancer type object by its name.
     * @see https://docs.hetzner.cloud/#load-balancer-types-get-a-load-balancer-type
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?LoadBalancerType
    {
        /** @var LoadBalancerTypes $loadBalancerTypes */
        $loadBalancerTypes = $this->list(new LoadBalancerTypeRequestOpts($name));

        return (count($loadBalancerTypes->load_balancer_types) > 0) ? $loadBalancerTypes->load_balancer_types[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->load_balancer_types = collect($input)->map(function ($loadBalancerType) {
            return LoadBalancerType::parse($loadBalancerType);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one'  => 'load_balancer_type',
                'many' => 'load_balancer_types'
        ];
    }
}
