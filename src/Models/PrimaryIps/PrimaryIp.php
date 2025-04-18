<?php

namespace LKDev\HetznerCloud\Models\PrimaryIps;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Datacenters\Datacenter;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\Models\Protection;

class PrimaryIp extends Model implements Resource
{
    public function __construct(
        public int                   $id,
        public                  string                $name,
        public                  string                $created,
        public                  string                $ip,
        public                  string                $type,
        public                  array                 $dns_ptr,
        public                  bool                  $blocked,
        public                  array|Protection|null $protection,
        public                  array                 $labels,
        public                  Datacenter|array      $datacenter,
        public                  string                $assignee_type,
        public                  ?int                  $assignee_id = null,
        public                  bool                  $auto_delete = false)
    {
        parent::__construct();
    }

    /**
     * Update the Primary IP.
     * @see https://docs.hetzner.cloud/#primary-ips-update-a-primary-ip
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?self
    {
        $response = $this->httpClient->put('primary_ips/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return self::parse(json_decode((string)$response->getBody())->primary_ip);
        }

        return null;
    }

    /**
     * The Primary IP may be assigned to a Server. In this case it is unassigned automatically.
     * The Server must be powered off (status off) in order for this operation to succeed.
     * @see https://docs.hetzner.cloud/#primary-ips-delete-a-primary-ip
     * @throws APIException|GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('primary_ips/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return true;
        }

        return false;
    }

    public static function parse($input): ?static
    {
        if ($input == null) {
            return null;
        }

        return new self($input->id, $input->name, $input->created, $input->ip, $input->type, $input->dns_ptr, $input->blocked, Protection::parse($input->protection), get_object_vars($input->labels), Datacenter::parse($input->datacenter), $input->assignee_type, $input->assignee_id, $input->auto_delete);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->primaryIps()->get($this->id);
    }

    /**
     * Assigns a Primary IP to a Server.
     * @see https://docs.hetzner.cloud/#primary-ip-actions-assign-a-primary-ip-to-a-resource
     * @throws APIException|GuzzleException
     */
    public function assignTo(int $assigneeId, string $assigneeType): ?APIResponse
    {
        $response = $this->httpClient->post('primary_ips/' . $this->id . '/actions/assign', [
            'json' => [
                'assignee_id'   => $assigneeId,
                'assignee_type' => $assigneeType,
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
     * Unassigns a Primary IP from a Server.
     * The Server must be powered off (status off) in order for this operation to succeed.
     * @see https://docs.hetzner.cloud/#primary-ip-actions-unassign-a-primary-ip-from-a-resource
     * @throws APIException|GuzzleException
     */
    public function unassign(): ?APIResponse
    {
        $response = $this->httpClient->post('primary_ips/' . $this->id . '/actions/unassign');
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Changes the hostname that will appear when getting the hostname belonging to this Primary IP.
     * @see https://docs.hetzner.cloud/#primary-ip-actions-change-reverse-dns-entry-for-a-primary-ip
     * @throws APIException|GuzzleException
     */
    public function changeReverseDNS(string $ip, ?string $dnsPtr = null): ?APIResponse
    {
        $response = $this->httpClient->post('primary_ips/' . $this->id . '/actions/change_dns_ptr', [
            'json' => [
                'ip'      => $ip,
                'dns_ptr' => $dnsPtr,
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
     * Changes the protection configuration of a Primary IP.
     * @see https://docs.hetzner.cloud/#primary-ip-actions-change-primary-ip-protection
     * @throws APIException|GuzzleException
     */
    public function changeProtection(bool $delete = true): ?APIResponse
    {
        $response = $this->httpClient->post('primary_ips/' . $this->id . '/actions/change_protection', [
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
}
