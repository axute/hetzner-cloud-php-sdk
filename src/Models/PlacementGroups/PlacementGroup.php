<?php

namespace LKDev\HetznerCloud\Models\PlacementGroups;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Servers\ServerReference;

/**
 * @property ServerReference[] $servers
 */
class PlacementGroup extends PlacementGroupReference implements Resource
{

    public function __construct(
        int            $id,
        ?string        $name = null,
        public ?string $type = null,
        public array   $servers = [],
        public array   $labels = [],
        public ?string $created = null,
        ?Client        $httpClient = null)
    {
        parent::__construct(id: $id, name: $name, httpClient: $httpClient);
    }

    public static function parse($input): null|static
    {
        if($input === null) {
            return null;
        }
        return new self(
            id: $input->id,
            name: $input->name,
            type: $input->type,
            servers: collect($input->servers)
                ->map(function ($id) {
                    return new ServerReference(id: $id);
                })->toArray(),
            labels: get_object_vars($input->labels),
            created: $input->created,
        );
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->placementGroups()->get($this->id);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function delete(): ?APIResponse
    {
        $response = $this->httpClient->delete('placement_groups/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function update(array $data): ?APIResponse
    {
        $response = $this->httpClient->put('placement_groups/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'placement_group' => self::parse(json_decode((string)$response->getBody())->network),
            ], $response->getHeaders());
        }

        return null;
    }
}
