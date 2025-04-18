<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 20:59.
 */

namespace LKDev\HetznerCloud\Models\Firewalls;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\Models\Servers\ServerReference;

/**
 * @property FirewallRule[] $firewallRules
 * @property FirewallResource[] $applied_to
 */
class Firewall extends Model implements Resource
{
    public function __construct(
        public int    $id,
        public string $name = '',
        public array  $rules = [],
        public array  $applied_to = [],
        public array  $labels = [],
        public string $created = '',
    )
    {
        parent::__construct();
    }

    /**
     * Update a Firewall.
     * @see https://docs.hetzner.cloud/#firewalls-update-a-firewall
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?static
    {
        $response = $this->httpClient->put('firewalls/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return self::parse(json_decode((string)$response->getBody())->firewall);
        }

        return null;
    }

    public static function parse($input): ?static
    {
        if ($input == null) {
            return null;
        }
        $appliedTo = [];
        $rules = [];

        foreach ($input->rules as $r) {
            $rules[] = new FirewallRule(
                direction: $r->direction,
                protocol: $r->protocol,
                source_ips: $r->source_ips,
                destination_ips: $r->destination_ips,
                port: (string)$r->port,
                description: $r->description);
        }

        foreach ($input->applied_to as $a) {
            if ($a->type === 'server') {
                $appliedTo[] = new FirewallResource(
                    type: $a->type,
                    server: new ServerReference(id: $a->server->id));
            }
        }

        return new self(id: $input->id,
            name: $input->name,
            rules: $rules,
            applied_to: $appliedTo,
            labels: get_object_vars($input->labels),
            created: $input->created);
    }

    /**
     * Sets the rules of a Firewall.
     * @see https://docs.hetzner.cloud/#firewall-actions-set-rules
     * @param FirewallRule[] $rules
     * @throws APIException|GuzzleException
     */
    public function setRules(array $rules): ?ApiResponse
    {
        $response = $this->httpClient->post('firewalls/' . $this->id . '/actions/set_rules', [
            'json' => [
                'rules' => collect($rules)->map(function ($r) {
                    return $r->toRequestSchema();
                }),
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'actions' => collect($payload->actions)->map(function ($action) {
                    return Action::parse($action);
                })->toArray(),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Deletes a Firewall.
     * @see https://docs.hetzner.cloud/#firewalls-delete-a-firewall
     * @throws APIException|GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('firewalls/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return true;
        }

        return false;
    }

    /**
     * Applies one Firewall to multiple resources.
     *
     * @see https://docs.hetzner.cloud/#firewall-actions-apply-to-resources
     *
     * @param FirewallResource[] $resources
     * @return APIResponse|null
     *
     * @throws APIException|GuzzleException
     */
    public function applyToResources(array $resources): ?APIResponse
    {
        $response = $this->httpClient->post('firewalls/' . $this->id . '/actions/apply_to_resources', [
            'json' => [
                'apply_to' => collect($resources)->map(function ($r) {
                    return $r->toRequestSchema();
                }),
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'actions' => collect($payload->actions)->map(function ($action) {
                    return Action::parse($action);
                })->toArray(),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Removes one Firewall from multiple resources.
     * @see https://docs.hetzner.cloud/#firewall-actions-remove-from-resources
     * @param FirewallResource[] $resources
     * @throws APIException|GuzzleException
     */
    public function removeFromResources(array $resources): ?APIResponse
    {
        $response = $this->httpClient->post('firewalls/' . $this->id . '/actions/remove_from_resources', [
            'json' => [
                'remove_from' => collect($resources)->map(function ($r) {
                    return $r->toRequestSchema();
                }),
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'actions' => collect($payload->actions)->map(function ($action) {
                    return Action::parse($action);
                })->toArray(),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->firewalls()->get($this->id);
    }
}
