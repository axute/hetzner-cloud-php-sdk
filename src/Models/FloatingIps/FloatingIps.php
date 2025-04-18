<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 20:59.
 */

namespace LKDev\HetznerCloud\Models\FloatingIps;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Locations\Location;
use LKDev\HetznerCloud\Models\Locations\LocationReference;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\Models\Servers\Server;
use LKDev\HetznerCloud\Models\Servers\ServerReference;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class FloatingIps extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $floating_ips;

    /**
     * Returns all floating ip objects.
     * @see https://docs.hetzner.cloud/#resources-floating-ips-get
     * @throws GuzzleException|APIException
     */
    public function all(FloatingIPRequestOpts|RequestOpts|null $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new FloatingIPRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all floating ip objects.
     * @see https://docs.hetzner.cloud/#resources-floating-ips-get
     * @throws APIException|GuzzleException
     */
    public function list(FloatingIPRequestOpts|RequestOpts|null $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new FloatingIPRequestOpts();
        }
        $response = $this->httpClient->get('floating_ips'.$requestOpts->buildQuery());
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
     * Returns a specific floating ip object.
     * @see https://docs.hetzner.cloud/#resources-floating-ips-get-1
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?FloatingIp
    {
        $response = $this->httpClient->get('floating_ips/'.$id);
        if (! HetznerAPIClient::hasError($response)) {
            return FloatingIp::parse(json_decode((string) $response->getBody())->{$this->_getKeys()['one']});
        }

        return null;
    }

    /**
     * Returns a specific Floating IP object by its name.
     * @see https://docs.hetzner.cloud/#resources-floating-ips-get-1
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?FloatingIp
    {
        /** @var FloatingIps $resp */
        $resp = $this->list(new FloatingIPRequestOpts($name));

        return (count($resp->floating_ips) > 0) ? $resp->floating_ips[0] : null;
    }

    /**
     * Creates a new Floating IP assigned to a server.
     * @see https://docs.hetzner.cloud/#resources-floating-ips-post
     * @throws APIException|GuzzleException
     */
    public function create(
        string $type,
        ?string $description = null,
        ?LocationReference $location = null,
        ?ServerReference $server = null,
        ?string $name = null,
        array $labels = []
    ): ?FloatingIp {
        $parameters = [
            'type' => $type,
        ];
        if ($description != null) {
            $parameters['description'] = $description;
        }
        if ($name != null) {
            $parameters['name'] = $name;
        }
        if ($location != null) {
            $parameters['home_location'] = $location->name;
        }
        if ($server != null) {
            $parameters['server'] = $server->id ?: $server->name;
        }
        if (! empty($labels)) {
            $parameters['labels'] = $labels;
        }
        $response = $this->httpClient->post('floating_ips', [
            'json' => $parameters,
        ]);
        if (! HetznerAPIClient::hasError($response)) {
            return FloatingIp::parse(json_decode((string) $response->getBody())->{$this->_getKeys()['one']});
        }

        return null;
    }

    public function setAdditionalData($input):static
    {
        $this->floating_ips = collect($input)->map(function ($floatingIp) {
            return FloatingIp::parse($floatingIp);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one' => 'floating_ip', 'many' => 'floating_ips'];
    }
}
