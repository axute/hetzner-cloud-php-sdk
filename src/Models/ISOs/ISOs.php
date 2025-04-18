<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 21:02.
 */

namespace LKDev\HetznerCloud\Models\ISOs;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class ISOs extends Model implements Resources
{
    use GetFunctionTrait;

    public array $isos;

    /**
     * Returns all iso objects.
     * @see https://docs.hetzner.cloud/#resources-isos-get
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new ISORequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all iso objects.
     * @see https://docs.hetzner.cloud/#resources-isos-get
     *
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }
        $response = $this->httpClient->get('isos' . $requestOpts->buildQuery());
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
     * Returns a specific iso object.
     *
     * @see https://docs.hetzner.cloud/#resources-iso-get-1
     * @throws APIException
     * @throws GuzzleException
     */
    public function getById(int $id): ?ISO
    {
        $response = $this->httpClient->get('isos/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return ISO::parse(json_decode((string)$response->getBody())->iso);
        }

        return null;
    }

    /**
     * Returns a specific iso object by its name.
     * @see https://docs.hetzner.cloud/#resources-iso-get-1
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?ISO
    {
        /** @var ISOs $resp */
        $resp = $this->list(new ISORequestOpts($name));

        return (count($resp->isos) > 0) ? $resp->isos[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->isos = collect($input)->map(function ($iso) {
            return ISO::parse($iso);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one'  => 'iso',
                'many' => 'isos'
        ];
    }
}
