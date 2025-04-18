<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 20:58.
 */

namespace LKDev\HetznerCloud\Models\Servers\Types;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class ServerTypes extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $server_types;

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }
        $response = $this->httpClient->get('server_types' . $requestOpts->buildQuery());
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
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?ServerType
    {
        $response = $this->httpClient->get('server_types/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return ServerType::parse(json_decode((string)$response->getBody())->server_type);
        }

        return null;
    }

    /**
     * Returns a specific server type object by its name.
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?ServerType
    {
        /** @var ServerTypes $serverTypes */
        $serverTypes = $this->list(new ServerTypesRequestOpts($name));

        return (count($serverTypes->server_types) > 0) ? $serverTypes->server_types[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->server_types = collect($input)->map(function ($serverType) {
            return ServerType::parse($serverType);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one'  => 'server_type',
                'many' => 'server_types'
        ];
    }
}
