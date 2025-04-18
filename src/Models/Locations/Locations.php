<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 21:00.
 */

namespace LKDev\HetznerCloud\Models\Locations;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class Locations extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $locations;

    /**
     * Returns all location objects.
     * @see https://docs.hetzner.cloud/#resources-locations-get
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new LocationRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all location objects.
     * @see https://docs.hetzner.cloud/#resources-locations-get
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new LocationRequestOpts();
        }
        $response = $this->httpClient->get('locations'.$requestOpts->buildQuery());
        if (! HetznerAPIClient::hasError($response)) {
            $resp = json_decode((string) $response->getBody());

            return APIResponse::create([
                'meta' => Meta::parse($resp->meta),
                $this->_getKeys()['many'] => self::parse($resp->{$this->_getKeys()['many']})->{$this->_getKeys()['many']},
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Returns a specific location object.
     * @see https://docs.hetzner.cloud/#resources-locations-get-1
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?Location
    {
        $response = $this->httpClient->get('locations/'.$id);
        if (! HetznerAPIClient::hasError($response)) {
            return Location::parse(json_decode((string) $response->getBody())->location);
        }

        return null;
    }

    /**
     * Returns a specific location object by its name.
     *
     * @see https://docs.hetzner.cloud/#resources-locations-get-1
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?Location
    {
        /** @var Locations $locations */
        $locations = $this->list(new LocationRequestOpts($name));

        return (count($locations->locations) > 0) ? $locations->locations[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->locations = collect($input)->map(function ($location) {
            return Location::parse($location);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one' => 'location', 'many' => 'locations'];
    }
}
