<?php

namespace LKDev\HetznerCloud\Models\PrimaryIps;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Datacenters\Datacenter;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class PrimaryIps extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $primary_ips;

    /**
     * Returns all primary ip objects.
     * @see https://docs.hetzner.cloud/#primary-ips-get-all-primary-ips
     * @throws GuzzleException|APIException
     */
    public function all(PrimaryIPRequestOpts|RequestOpts|null $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new PrimaryIPRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all primary ip objects.
     * @see https://docs.hetzner.cloud/#primary-ips-get-all-primary-ips
     * @throws APIException|GuzzleException
     */
    public function list(PrimaryIPRequestOpts|RequestOpts|null $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new PrimaryIPRequestOpts();
        }
        $response = $this->httpClient->get('primary_ips'.$requestOpts->buildQuery());
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
     * Returns a specific Primary IP object.
     * @see https://docs.hetzner.cloud/#primary-ips-get-a-primary-ip
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?PrimaryIp
    {
        $response = $this->httpClient->get('primary_ips/'.$id);
        if (! HetznerAPIClient::hasError($response)) {
            return PrimaryIp::parse(json_decode((string) $response->getBody())->{$this->_getKeys()['one']});
        }

        return null;
    }

    /**
     * Returns a specific Primary IP object by its name.
     * @see https://docs.hetzner.cloud/#primary-ips-get-a-primary-ip
     * @throws APIException
     * @throws GuzzleException
     */
    public function getByName(string $name): ?PrimaryIp
    {
        /** @var PrimaryIps $resp */
        $resp = $this->list(new PrimaryIPRequestOpts($name));

        return (count($resp->primary_ips) > 0) ? $resp->primary_ips[0] : null;
    }

    /**
     * Creates a new Primary IP, optionally assigned to a Server.
     * @see https://docs.hetzner.cloud/#primary-ips-create-a-primary-ip
     * @throws APIException|GuzzleException
     */
    public function create(
        string $type,
        string $name,
        string $assigneeType,
        bool $autoDelete = false,
        ?int $assigneeId = null,
        ?Datacenter $datacenter = null,
        array $labels = []
    ): ?PrimaryIp {
        $parameters = [
            'type' => $type,
            'name' => $name,
            'assignee_type' => $assigneeType,
            'auto_delete' => $autoDelete,
        ];
        if ($assigneeId != null) {
            $parameters['assignee_id'] = $assigneeId;
        }
        if ($datacenter != null) {
            $parameters['datacenter'] = $datacenter->id ?: $datacenter->name;
        }
        if (! empty($labels)) {
            $parameters['labels'] = $labels;
        }
        $response = $this->httpClient->post('primary_ips', [
            'json' => $parameters,
        ]);
        if (! HetznerAPIClient::hasError($response)) {
            return PrimaryIp::parse(json_decode((string) $response->getBody())->{$this->_getKeys()['one']});
        }

        return null;
    }

    public function setAdditionalData($input):static
    {
        $this->primary_ips = collect($input)->map(function ($primaryIp) {
            return PrimaryIp::parse($primaryIp);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one' => 'primary_ip', 'many' => 'primary_ips'];
    }
}
