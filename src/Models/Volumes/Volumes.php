<?php

/**
 * Created by PhpStorm.
 * User: lkaemmerling
 * Date: 2018-09-20
 * Time: 15:58.
 */

namespace LKDev\HetznerCloud\Models\Volumes;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Locations\Location;
use LKDev\HetznerCloud\Models\Locations\LocationReference;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\Models\Servers\Server;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

/**
 * Class Volumes.
 */
class Volumes extends Model implements Resources
{
    use GetFunctionTrait;

    public array $volumes;

    /**
     * Returns all existing volume objects.
     * @see https://docs.hetzner.cloud/#resources-volumes-get
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all existing volume objects.
     * @see https://docs.hetzner.cloud/#resources-volumes-get
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new VolumeRequestOpts();
        }
        $response = $this->httpClient->get('volumes' . $requestOpts->buildQuery());
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
     * Returns a specific server object by its name. The server must exist inside the project.
     * @see https://docs.hetzner.cloud/#resources-volumes-get
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?Volume
    {
        /** @var Volumes $volumes */
        $volumes = $this->list(new VolumeRequestOpts($name));

        return (count($volumes->volumes) > 0) ? $volumes->volumes[0] : null;
    }

    /**
     * Returns a specific volume object. The server must exist inside the project.
     * @see https://docs.hetzner.cloud/#resources-volume-get-1
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?Volume
    {
        $response = $this->httpClient->get('volumes/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return Volume::parse(json_decode((string)$response->getBody())->volume);
        }

        return null;
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function create(string $name, int $size, ?Server $server = null, ?LocationReference $location = null, bool $automount = false, ?string $format = null, array $labels = []): ?APIResponse
    {
        $parameters = [
            'name'      => $name,
            'size'      => $size,
            'automount' => $automount,
        ];
        if ($location == null && $server != null) {
            $parameters['server'] = $server->id;
        } elseif ($location != null && $server == null) {
            $parameters['location'] = $location->name ?: $location->id;
        } else {
            throw new InvalidArgumentException('Please specify only a server or a location');
        }
        if ($format != null) {
            $parameters['format'] = $format;
        }
        if (!empty($labels)) {
            $parameters['labels'] = $labels;
        }
        $response = $this->httpClient->post('volumes', [
            'json' => $parameters,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $data = json_decode((string)$response->getBody());

            return APIResponse::create([
                'action'       => Action::parse($data->action),
                'volume'       => Volume::parse($data->volume),
                'next_actions' => collect($data->next_actions)->map(function ($action) {
                    return Action::parse($action);
                })->toArray(),
            ], $response->getHeaders());
        }

        return null;
    }

    public function setAdditionalData($input): static
    {
        $this->volumes = collect($input)->map(function ($volume) {
            if ($volume != null) {
                return Volume::parse($volume);
            }

            return null;
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one'  => 'volume',
                'many' => 'volumes'
        ];
    }
}
