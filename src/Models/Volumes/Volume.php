<?php

/**
 * Created by PhpStorm.
 * User: lkaemmerling
 * Date: 2018-09-20
 * Time: 15:58.
 */

namespace LKDev\HetznerCloud\Models\Volumes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Locations\Location;
use LKDev\HetznerCloud\Models\Protection;
use LKDev\HetznerCloud\Models\Servers\Server;
use LKDev\HetznerCloud\Models\Servers\ServerReference;

/**
 * Class Volume.
 */
class Volume extends VolumeReference implements Resource
{
    public string $name;
    public int $size;
    public Server|int $server;
    public Location $location;
    public Protection|null $protection;
    public array $labels;
    public string $linux_device;

    public function __construct(
        ?int    $id = null,
        ?Client $httpClient = null)
    {
        parent::__construct(id: $id, httpClient: $httpClient);
    }

    public function setAdditionalData($data): static
    {
        $this->id = $data->id;
        $this->name = $data->name;
        $this->linux_device = $data->linux_device;
        $this->size = $data->size;

        $this->server = $data->server;
        $this->location = Location::parse($data->location);
        $this->protection = Protection::parse($data?->protection);
        $this->labels = get_object_vars($data->labels);

        return $this;
    }

    /**
     * Deletes a volume. This immediately removes the volume from your account, and it is no longer accessible.
     * @see https://docs.hetzner.cloud/#resources-servers-delete
     * @throws APIException
     * @throws GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('volumes/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     * @see https://docs.hetzner.cloud/#volume-actions-attach-volume-to-a-server
     */
    public function attach(ServerReference $server, bool $automount = null): ?APIResponse
    {
        $payload = [
            'server' => $server->id,
        ];
        if ($automount !== null) {
            $payload['automount'] = $automount;
        }

        $response = $this->httpClient->post('volumes/' . $this->id . '/actions/attach', [
            'json' => $payload,
        ]);

        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @see https://docs.hetzner.cloud/#volume-actions-detach-volume
     * @throws APIException|GuzzleException
     */
    public function detach(): ?APIResponse
    {
        $response = $this->httpClient->post('volumes/' . $this->id . '/actions/detach');
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @see https://docs.hetzner.cloud/#volume-actions-resize-volume
     * @throws APIException|GuzzleException
     */
    public function resize(int $size): ?APIResponse
    {
        $response = $this->httpClient->post('volumes/' . $this->id . '/actions/resize', [
            'json' => [
                'size' => $size,
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Update a volume with new meta data.
     * @see https://docs.hetzner.cloud/#resources-volume-put
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?APIResponse
    {
        $response = $this->httpClient->put('volumes/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'volume' => self::parse(json_decode((string)$response->getBody())->volume),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Changes the protection configuration of the volume.
     * @see https://docs.hetzner.cloud/#volume-actions-change-volume-protection
     * @throws APIException|GuzzleException
     */
    public function changeProtection(bool $delete = true): ?APIResponse
    {
        $response = $this->httpClient->post('volumes/' . $this->id . '/actions/change_protection', [
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

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return (new self($input->id))->setAdditionalData($input);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->volumes()->get($this->id);
    }
}
