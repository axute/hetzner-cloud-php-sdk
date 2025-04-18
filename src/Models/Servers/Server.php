<?php

namespace LKDev\HetznerCloud\Models\Servers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Datacenters\Datacenter;
use LKDev\HetznerCloud\Models\Images\Image;
use LKDev\HetznerCloud\Models\Images\ImageReference;
use LKDev\HetznerCloud\Models\ISOs\ISO;
use LKDev\HetznerCloud\Models\LoadBalancers\LoadBalancerReference;
use LKDev\HetznerCloud\Models\Networks\NetworkReference;
use LKDev\HetznerCloud\Models\PlacementGroups\PlacementGroup;
use LKDev\HetznerCloud\Models\Protection;
use LKDev\HetznerCloud\Models\Servers\Types\ServerType;
use LKDev\HetznerCloud\Models\Volumes\Volume;

/**
 * @property LoadBalancerReference[] $load_balancers
 */
class Server extends ServerReference implements Resource
{
    public function __construct(
        int                    $id,
        string                 $name,
        public string          $status,
        public string          $created,
        public object          $public_net,
        public array           $private_net,
        public ServerType      $server_type,
        public Datacenter      $datacenter,
        public ?Image          $image,
        public ?ISO            $iso,
        public int             $primary_disk_size,
        public ?string         $backup_window,
        public bool            $rescue_enabled = false,
        public bool            $locked = false,
        public ?int            $outgoing_traffic = null,
        public ?int            $ingoing_traffic = null,
        public ?int            $included_traffic = null,
        public Protection      $protection = new Protection(delete: false),
        public array           $labels = [],
        public array           $volumes = [],
        public array           $load_balancers = [],
        public ?PlacementGroup $placement_group = null,
        ?Client                $httpClient = null
    )
    {
        parent::__construct(id: $id, name: $name, httpClient: $httpClient);
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->servers()->get($this->id);
    }

    /**
     * Starts a server by turning its power on.
     * @see https://docs.hetzner.cloud/#server-actions-power-on-a-server
     * @throws APIException|GuzzleException
     */
    public function powerOn(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/poweron'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Reboots a server gracefully by sending an ACPI request. The server operating system must support ACPI and react to the request, otherwise the server will not reboot.
     * @see https://docs.hetzner.cloud/#server-actions-soft-reboot-a-server
     * @throws APIException|GuzzleException
     */
    public function softReboot(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/reboot'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Cuts power to a server and starts it again. This forcefully stops it without giving the server operating system time to gracefully stop. This may lead to data loss, it’s equivalent to pulling the power cord and plugging it in again. Reset should only be used when reboot does not work.
     * @see https://docs.hetzner.cloud/#server-actions-reset-a-server
     * @throws APIException|GuzzleException
     */
    public function reset(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/reset'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Shuts down a server gracefully by sending an ACPI shutdown request. The server operating system must support ACPI and react to the request, otherwise the server will not shut down.
     * @see https://docs.hetzner.cloud/#server-actions-shutdown-a-server
     * @throws APIException|GuzzleException
     */
    public function shutdown(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/shutdown'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Cuts power to the server. This forcefully stops it without giving the server operating system time to gracefully stop. May lead to data loss, equivalent to pulling the power cord. Power off should only be used when shutdown does not work.
     * @see https://docs.hetzner.cloud/#server-actions-power-off-a-server
     * @throws APIException|GuzzleException
     */
    public function powerOff(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/poweroff'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Resets the root password. Only works for Linux systems that are running the qemu guest agent. Server must be powered on (state on) in order for this operation to succeed.
     * @see https://docs.hetzner.cloud/#server-actions-reset-root-password-of-a-server
     * @throws APIException|GuzzleException
     */
    public function resetRootPassword(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/reset_password'));
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'action'        => Action::parse($payload->action),
                'root_password' => $payload->root_password,
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Enable the Hetzner Rescue System for this server. The next time a Server with enabled rescue mode boots it will start a special minimal Linux distribution designed for repair and reinstall.
     * @see https://docs.hetzner.cloud/#server-actions-enable-rescue-mode-for-a-server
     * @throws APIException|GuzzleException
     */
    public function enableRescue(string $type = 'linux64', array $ssh_keys = []): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/enable_rescue'), [
            'json' => [
                'type'     => $type,
                'ssh_keys' => $ssh_keys,
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'action'        => Action::parse($payload->action),
                'root_password' => $payload->root_password,
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Disables the Hetzner Rescue System for a server. This makes a server start from its disks on next reboot.
     * @see https://docs.hetzner.cloud/#server-actions-disable-rescue-mode-for-a-server
     * @return APIResponse|null
     * @throws APIException|GuzzleException
     */
    public function disableRescue(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/disable_rescue'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Creates an image (snapshot) from a server by copying the contents of its disks. This creates a snapshot of the current state of the disk and copies it into an image. If the server is currently running you must make sure that its disk content is consistent. Otherwise, the created image may not be readable.
     * @see https://docs.hetzner.cloud/#server-actions-create-image-from-a-server
     * @throws APIException|GuzzleException
     */
    public function createImage(string $description = '', string $type = 'snapshot', array $labels = []): ?APIResponse
    {
        $payload = [
            'description' => $description,
            'type'        => $type,
        ];
        if (!empty($labels)) {
            $payload['labels'] = $labels;
        }
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/create_image'), [
            'json' => $payload,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'action' => Action::parse($payload->action),
                'image'  => Image::parse($payload->image),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Rebuilds a server overwriting its disk with the content of an image, thereby destroying all data on the target server.
     * @see https://docs.hetzner.cloud/#server-actions-rebuild-a-server-from-an-image
     * @throws APIException|GuzzleException
     */
    public function rebuildFromImage(ImageReference $image): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/rebuild'), [
            'json' => [
                'image' => $image->id ?: $image->name,
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create(array_merge([
                'action' => Action::parse($payload->action),
            ], (property_exists($payload, 'root_password')) ? ['root_password' => $payload->root_password] : []), $response->getHeaders());
        }

        return null;
    }

    /**
     * Changes the type (Cores, RAM and disk sizes) of a server.
     * @see https://docs.hetzner.cloud/#server-actions-change-the-type-of-a-server
     * @throws APIException|GuzzleException
     */
    public function changeType(ServerType $serverType, bool $upgradeDisk = false): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/change_type'), [
            'json' => [
                'server_type'  => $serverType->name,
                'upgrade_disk' => $upgradeDisk,
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
     * Enables and configures the automatic daily backup option for the server. Enabling automatic backups will increase the price of the server by 20%.
     * @see https://docs.hetzner.cloud/#server-actions-enable-and-configure-backups-for-a-server
     * @throws APIException|GuzzleException
     */
    public function enableBackups(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/enable_backup'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Disables the automatic backup option and deletes all existing Backups for a Server.
     * @see https://docs.hetzner.cloud/#server-actions-disable-backups-for-a-server
     * @throws APIException|GuzzleException
     */
    public function disableBackups(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/disable_backup'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Attaches an ISO to a server. The Server will immediately see it as a new disk. An already attached ISO will automatically be detached before the new ISO is attached.
     * @see https://docs.hetzner.cloud/#server-actions-attach-an-iso-to-a-server
     * @throws APIException|GuzzleException
     */
    public function attachISO(ISO $iso): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/attach_iso'), [
            'json' => [
                'iso' => $iso->name ?: $iso->id,
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
     * Detaches an ISO from a server. In case no ISO image is attached to the server, the status of the returned action is immediately set to success.
     * @see https://docs.hetzner.cloud/#server-actions-detach-an-iso-from-a-server
     * @throws APIException|GuzzleException
     */
    public function detachISO(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/detach_iso'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Changes the hostname that will appear when getting the hostname belonging to the primary IPs (ipv4 and ipv6) of this server.
     * @see https://docs.hetzner.cloud/#server-actions-change-reverse-dns-entry-for-this-server
     * @throws APIException|GuzzleException
     */
    public function changeReverseDNS(string $ip, ?string $dnsPtr = null): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/change_dns_ptr'), [
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
     * Get Metrics for specified server.
     * @see https://docs.hetzner.cloud/#servers-get-metrics-for-a-server
     * @throws APIException|GuzzleException
     */
    public function metrics(string $type, string $start, string $end, ?int $step = null): ?APIResponse
    {
        $response = $this->httpClient->get($this->replaceServerIdInUri('servers/{id}/metrics?') . http_build_query(compact('type', 'start', 'end', 'step')));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'metrics' => json_decode((string)$response->getBody())->metrics,
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Deletes a server. This immediately removes the server from your account, and it is no longer accessible.
     * @see https://docs.hetzner.cloud/#resources-servers-delete
     * @throws APIException|GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete($this->replaceServerIdInUri('servers/{id}'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Update a server with new meta data.
     * @see https://docs.hetzner.cloud/#resources-servers-put
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?APIResponse
    {
        $response = $this->httpClient->put($this->replaceServerIdInUri('servers/{id}'), [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'server' => self::parse(json_decode((string)$response->getBody())->server),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Requests credentials for remote access via vnc over websocket to keyboard, monitor, and mouse for a server.
     * @see https://docs.hetzner.cloud/#resources-server-actions-post-16
     * @throws APIException|GuzzleException
     */
    public function requestConsole(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/request_console'));
        if (!HetznerAPIClient::hasError($response)) {
            $payload = json_decode((string)$response->getBody());

            return APIResponse::create([
                'action'   => Action::parse($payload->action),
                'wss_url'  => $payload->wss_url,
                'password' => $payload->password,
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Changes the protection configuration of the server.
     * @see https://docs.hetzner.cloud/#server-actions-change-server-protection
     * @throws APIException|GuzzleException
     */
    public function changeProtection(bool $delete = true, bool $rebuild = true): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/change_protection'), [
            'json' => [
                'delete'  => $delete,
                'rebuild' => $rebuild,
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
     * @throws APIException|GuzzleException
     * @see https://docs.hetzner.cloud/#server-actions-attach-a-server-to-a-network
     */
    public function attachToNetwork(NetworkReference $network, ?string $ip = null, array $aliasIps = []): ?APIResponse
    {
        $payload = [
            'network' => $network->id,
        ];
        if ($ip != null) {
            $payload['ip'] = $ip;
        }
        if ($ip != null) {
            $payload['alias_ips'] = $aliasIps;
        }
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/attach_to_network'), [
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
     * @throws APIException|GuzzleException
     */
    public function detachFromNetwork(NetworkReference $network): ?APIResponse
    {
        $payload = [
            'network' => $network->id,
        ];

        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/detach_from_network'), [
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
     * @throws APIException|GuzzleException
     */
    public function changeAliasIPs(NetworkReference $network, array $aliasIps): ?APIResponse
    {
        $payload = [
            'network'   => $network->id,
            'alias_ips' => $aliasIps,
        ];
        $response = $this->httpClient->post($this->replaceServerIdInUri('servers/{id}/actions/change_alias_ips'), [
            'json' => $payload,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    protected function replaceServerIdInUri(string $uri): string
    {
        return str_replace('{id}', $this->id, $uri);
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }
        return (new self(
            id: $input->id,
            name: $input->name,
            status: $input->status,
            created: $input->created,
            public_net: $input->public_net,
            private_net: $input->private_net,
            server_type: ServerType::parse($input->server_type),
            datacenter: Datacenter::parse($input->datacenter),
            image: Image::parse($input->image ?? null),
            iso: ISO::parse($input->iso ?? null),
            primary_disk_size: $input->primary_disk_size,
            backup_window: $input->backup_window ?? null,
            rescue_enabled: $input->rescue_enabled,
            locked: $input->locked,
            outgoing_traffic: $input->outgoing_traffic ?: null,
            ingoing_traffic: $input->ingoing_traffic ?: null,
            included_traffic: $input->included_traffic ?: null,
            protection: Protection::parse($input->protection),
            labels: get_object_vars($input->labels),
            volumes: collect($input->volumes)
                ->map(function ($id) {
                    return new Volume(id: $id);
                })->toArray(),
            load_balancers: collect($input->load_balancers)->map(function ($id) {
                return new LoadBalancerReference(id: $id);
            })->toArray(),
            placement_group: PlacementGroup::parse($input->placement_group ?? null),
        ));
    }
}
