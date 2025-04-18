<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 21:01.
 */

namespace LKDev\HetznerCloud\Models\Datacenters;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;
use stdClass;

/**
 * Class Datacenters.
 */
class Datacenters extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $datacenters;

    /**
     * Returns all datacenter objects.
     * @see https://docs.hetzner.cloud/#resources-datacenters-get
     * @throws GuzzleException
     * @throws APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new DatacenterRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * List datacenter objects.
     * @see https://docs.hetzner.cloud/#resources-datacenters-get
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new DatacenterRequestOpts();
        }
        $response = $this->httpClient->get('datacenters' . $requestOpts->buildQuery());

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
     * Returns a specific datacenter object.
     *
     * @see https://docs.hetzner.cloud/#resources-datacenters-get-1
     * @throws APIException
     * @throws GuzzleException
     */
    public function getById(int $id): ?Datacenter
    {
        $response = $this->httpClient->get('datacenters/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return Datacenter::parse(json_decode((string)$response->getBody())->{$this->_getKeys()['one']});
        }

        return null;
    }

    /**
     * Returns a specific datacenter object by its name.
     * @see https://docs.hetzner.cloud/#resources-datacenters-get-1
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?Datacenter
    {
        /** @var Datacenters $resp */
        $resp = $this->list(new DatacenterRequestOpts($name));

        return (count($resp->datacenters) > 0) ? $resp->datacenters[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->datacenters = collect($input)->map(function ($datacenter) {
            return Datacenter::parse($datacenter);
        })->toArray();

        return $this;
    }

    public static function parse(stdClass|array|null $input): null|static
    {
        if ($input === null) {
            return null;
        }
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one'  => 'datacenter',
                'many' => 'datacenters'
        ];
    }
}
