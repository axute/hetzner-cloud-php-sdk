<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class LoadBalancers extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $load_balancers;

    /**
     * Gets all existing Load Balancers that you have available.
     * @see https://docs.hetzner.cloud/#load-balancers-get-all-load-balancers
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new LoadBalancerRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Gets all existing Load Balancers that you have available.
     * @see https://docs.hetzner.cloud/#load-balancers-get-all-load-balancers
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new LoadBalancerRequestOpts();
        }
        $response = $this->httpClient->get('load_balancers' . $requestOpts->buildQuery());
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
     * Gets a specific Load Balancer object.
     * @see https://docs.hetzner.cloud/#load-balancers-get-a-load-balancer
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?LoadBalancer
    {
        $response = $this->httpClient->get('load_balancers/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return LoadBalancer::parse(json_decode((string)$response->getBody())->load_balancer);
        }

        return null;
    }

    /**
     * Gets a specific Load Balancer object.
     * @see https://docs.hetzner.cloud/#load-balancers-get-a-load-balancer
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?LoadBalancer
    {
        /** @var LoadBalancers $loadBalancers */
        $loadBalancers = $this->list(new LoadBalancerRequestOpts($name));

        return (count($loadBalancers->load_balancers) > 0) ? $loadBalancers->load_balancers[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->load_balancers = collect($input)->map(function ($loadBalancer) {
            return LoadBalancer::parse($loadBalancer);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one'  => 'load_balancer',
                'many' => 'load_balancers'
        ];
    }
}
