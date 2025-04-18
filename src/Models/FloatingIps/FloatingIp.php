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
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Locations\Location;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\Models\Protection;
use LKDev\HetznerCloud\Models\Servers\ServerReference;

class FloatingIp extends Model implements Resource
{
    public function __construct(
        public int                   $id,
        public ?string               $description,
        public string                $ip,
        public string                $type,
        public int                   $server,
        public array                 $dns_ptr,
        public Location              $home_location,
        public bool                  $blocked = false,
        public Protection|array|null $protection = new Protection(false),
        public array                 $labels = [],
        public string                $created = '',
        public string                $name = ''
    )
    {
        parent::__construct();
    }

    /**
     * Update a Floating IP.
     *
     * @see https://docs.hetzner.cloud/#resources-floating-ips-put
     *
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?static
    {
        $response = $this->httpClient->put('floating_ips/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return self::parse(json_decode((string)$response->getBody())->floating_ip);
        }

        return null;
    }


    /**
     * Deletes a Floating IP. If it is currently assigned to a server it will automatically get unassigned.
     *
     * @see https://docs.hetzner.cloud/#resources-floating-ips-delete
     *
     * @throws APIException
     * @throws GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('floating_ips/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return true;
        }

        return false;
    }

    /**
     * Changes the protection configuration of the Floating IP.
     *
     * @see https://docs.hetzner.cloud/#resources-floating-ip-actions-post-3
     *
     * @throws APIException|GuzzleException
     */
    public function changeProtection(bool $delete = true): ?APIResponse
    {
        $response = $this->httpClient->post('floating_ips/' . $this->id . '/actions/change_protection', [
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

    /**
     * Assigns a Floating IP to a server.
     * @see https://docs.hetzner.cloud/#floating-ip-actions-assign-a-floating-ip-to-a-server
     * @throws APIException|GuzzleException
     */
    public function assignTo(ServerReference $server): ?APIResponse
    {
        $response = $this->httpClient->post('floating_ips/' . $this->id . '/actions/assign', [
            'json' => [
                'server' => $server->id,
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
     * Unassigns a Floating IP, resulting in it being unreachable. You may assign it to a server again at a later time.
     *
     * @see https://docs.hetzner.cloud/#floating-ip-actions-unassign-a-floating-ip
     *
     * @throws APIException|GuzzleException
     */
    public function unassign(): ?APIResponse
    {
        $response = $this->httpClient->post('floating_ips/' . $this->id . '/actions/unassign');
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Changes the hostname that will appear when getting the hostname belonging to this Floating IP.
     *
     * @see https://docs.hetzner.cloud/#floating-ip-actions-change-reverse-dns-entry-for-a-floating-ip
     *
     * @throws APIException
     * @throws GuzzleException
     */
    public function changeReverseDNS(string $ip, ?string $dnsPtr = null): ?APIResponse
    {
        $response = $this->httpClient->post('floating_ips/' . $this->id . '/actions/change_dns_ptr', [
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

    public static function parse($input): ?static
    {
        if ($input == null) {
            return null;
        }

        return new self(
            id: $input->id,
            description: $input->description,
            ip: $input->ip,
            type: $input->type,
            server: $input->server,
            dns_ptr: $input->dns_ptr,
            home_location: Location::parse($input->home_location),
            blocked: $input->blocked,
            protection: Protection::parse($input->protection),
            labels: get_object_vars($input->labels),
            created: $input->created,
            name: $input->name);
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->floatingIps()->get($this->id);
    }
}
