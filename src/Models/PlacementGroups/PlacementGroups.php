<?php

namespace LKDev\HetznerCloud\Models\PlacementGroups;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class PlacementGroups extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $placement_groups;

    /**
     * Returns all existing PlacementGroup objects.
     * @see https://docs.hetzner.cloud/#placement-groups-get-all-PlacementGroups
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new PlacementGroupRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all existing PlacementGroup objects.
     * @see https://docs.hetzner.cloud/#placement-groups-get-all-PlacementGroups
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new PlacementGroupRequestOpts();
        }
        $response = $this->httpClient->get('placement_groups'.$requestOpts->buildQuery());
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
     * Returns a specific PlacementGroup object. The PlacementGroup must exist inside the project.
     * @see https://docs.hetzner.cloud/#placement-groups-get-a-PlacementGroup
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?PlacementGroup
    {
        $response = $this->httpClient->get('placement_groups/'.$id);
        if (! HetznerAPIClient::hasError($response)) {
            return PlacementGroup::parse(json_decode((string) $response->getBody())->placement_group);
        }

        return null;
    }

    /**
     * Returns a specific PlacementGroup object by its name. The PlacementGroup must exist inside the project.
     * @see https://docs.hetzner.cloud/#placement-groups
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?PlacementGroup
    {
        /** @var PlacementGroups $placementGroups */
        $placementGroups = $this->list(new PlacementGroupRequestOpts($name));

        return (count($placementGroups->placement_groups) > 0) ? $placementGroups->placement_groups[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->placement_groups = collect($input)
            ->map(function ($placementGroup) {
                if ($placementGroup != null) {
                    return PlacementGroup::parse($placementGroup);
                }
                return null;
            })
            ->toArray();

        return $this;
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function create(string $name, string $type, array $labels = []): ?APIResponse
    {
        $payload = [
            'name' => $name,
            'type' => $type,
        ];
        if (! empty($labels)) {
            $payload['labels'] = $labels;
        }

        $response = $this->httpClient->post('placement_groups', [
            'json' => $payload,
        ]);
        if (! HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string) $response->getBody());

            return APIResponse::create([
                'placement_group' => PlacementGroup::parse($payload->placement_group),
            ], $response->getHeaders());
        }

        return null;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one' => 'placement_group', 'many' => 'placement_groups'];
    }
}
