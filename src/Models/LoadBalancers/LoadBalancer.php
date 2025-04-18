<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\LoadBalancerTypes\LoadBalancerType;
use LKDev\HetznerCloud\Models\Locations\Location;
use LKDev\HetznerCloud\Models\Protection;
use LKDev\HetznerCloud\Models\Servers\Server;
use stdClass;

class LoadBalancer extends LoadBalancerReference implements Resource
{
    public function __construct(
        int                   $id,
        public string                $name,
        public LoadBalancerAlgorithm $algorithm,
        public string                $created,
        public int                   $included_traffic,
        public array                 $labels,
        public LoadBalancerType      $loadBalancer_type,
        public Location              $location,
        public array                 $private_net,
        public ?Protection           $protection,
        public stdClass|array        $public_net,
        public array                 $services,
        public array                 $targets,
        public ?int                  $ingoing_traffic = null,
        public ?int                  $outgoing_traffic = null)
    {
        parent::__construct(id: $id);
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(
            id: $input->id,
            name: $input->name,
            algorithm: LoadBalancerAlgorithm::parse($input->algorithm),
            created: $input->created,
            included_traffic: $input->included_traffic,
            labels: get_object_vars($input->labels),
            loadBalancer_type: LoadBalancerType::parse($input->load_balancer_type),
            location: Location::parse($input->location),
            private_net: $input->private_net,
            protection: Protection::parse($input->protection),
            public_net: $input->public_net,
            services: $input->services,
            targets: $input->targets,
            ingoing_traffic: $input->ingoing_traffic,
            outgoing_traffic: $input->outgoing_traffic
        );
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->loadBalancers()->get($this->id);
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('load_balancers/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return true;
        }

        return false;
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function update(array $data): ?static
    {
        $response = $this->httpClient->put('load_balancers/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return self::parse(json_decode((string)$response->getBody())->load_balancer);
        }

        return null;
    }

    protected function replaceServerIdInUri(string $uri): string
    {
        return str_replace('{id}', $this->id, $uri);
    }

    /**
     * Adds a service to a Load Balancer.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-add-service
     * @throws APIException
     * @throws GuzzleException
     */
    public function addService(string $destinationPort, LoadBalancerHealthCheck $healthCheck, int $listenPort, string $protocol, string $proxyprotocol, ?LoadBalancerServiceHttp $http = null): ?APIResponse
    {
        $payload = [
            'destination_port' => $destinationPort,
            'health_check'     => $healthCheck,
            'listen_port'      => $listenPort,
            'protocol'         => $protocol,
            'proxyprotocol'    => $proxyprotocol,
        ];
        if ($http != null) {
            $payload['http'] = $http;
        }
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/add_service'), [
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
     * Adds a target to a Load Balancer.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-add-target
     * @throws APIException
     * @throws GuzzleException
     */
    public function addTarget(string $type, ?LoadBalancerTargetIp $ip = null, bool $usePrivateIp = false, array $labelSelector = [], ?Server $server = null): ?APIResponse
    {
        $payload = [
            'type'           => $type,
            'use_private_ip' => $usePrivateIp,
        ];
        if ($ip != null) {
            $payload['ip'] = $ip;
        }
        if (!empty($labelSelector)) {
            $payload['label_selector'] = $labelSelector;
        }
        if ($server != null) {
            $payload['server'] = $server;
        }
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/add_target'), [
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
     * Attach a Load Balancer to a Network.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-attach-a-load-balancer-to-a-network
     * @throws APIException
     * @throws GuzzleException
     */
    public function attachLoadBalancerToNetwork(int $network, string $ip = ''): ?APIResponse
    {
        $payload = [
            'network' => $network,
        ];
        if (!empty($ip)) {
            $payload['ip'] = $ip;
        }

        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/attach_to_network'), [
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
     * Change the algorithm that determines to which target new requests are sent.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-change-algorithm
     * @throws APIException
     * @throws GuzzleException
     */
    public function changeAlgorithm(string $type): ?APIResponse
    {
        $payload = [
            'type' => $type,
        ];
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/change_algorithm'), [
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
     * Changes the hostname that will appear when getting the hostname belonging to the public IPs (IPv4 and IPv6) of this Load Balancer.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-change-reverse-dns-entry-for-this-load-balancer
     * @throws APIException
     * @throws GuzzleException
     */
    public function changeReverseDnsEntry(string $dnsPtr, string $ip): ?APIResponse
    {
        $payload = [
            'dns_ptr' => $dnsPtr,
            'ip'      => $ip,
        ];
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/change_dns_ptr'), [
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
     * Changes the protection configuration of a Load Balancer.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-change-load-balancer-protection
     * @throws APIException
     * @throws GuzzleException
     */
    public function changeProtection(bool $delete = false): ?APIResponse
    {
        $payload = [
            'delete' => $delete,
        ];
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/change_protection'), [
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
     * Changes the type (Max Services, Max Targets and Max Connections) of a Load Balancer.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-change-the-type-of-a-load-balancer
     * @throws APIException
     * @throws GuzzleException
     */
    public function changeType(string $loadBalancerType): ?APIResponse
    {
        $payload = [
            'load_balancer_type' => $loadBalancerType,
        ];
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/change_type'), [
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
     * Delete a service of a Load Balancer.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-delete-service
     * @throws APIException
     * @throws GuzzleException
     */
    public function deleteService(int $listenPort): ?APIResponse
    {
        $payload = [
            'listen_port' => $listenPort,
        ];
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/delete_service'), [
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
     * Detaches a Load Balancer from a network.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-detach-a-load-balancer-from-a-network
     * @throws APIException
     * @throws GuzzleException
     */
    public function detachFromNetwork(int $network): ?APIResponse
    {
        $payload = [
            'network' => $network,
        ];
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/detach_from_network'), [
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
     * Disable the public interface of a Load Balancer. The Load Balancer will be not accessible from the internet via its public IPs.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-disable-the-public-interface-of-a-load-balancer
     * @throws APIException
     * @throws GuzzleException
     */
    public function disablePublicInterface(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/disable_public_interface'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Enable the public interface of a Load Balancer. The Load Balancer will be accessible from the internet via its public IPs.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-enable-the-public-interface-of-a-load-balancer
     * @throws APIException
     * @throws GuzzleException
     */
    public function enablePublicInterface(): ?APIResponse
    {
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/enable_public_interface'));
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Removes a target from a Load Balancer.
     * @see https://docs.hetzner.cloud/#load-balancer-actions-remove-target
     * @throws APIException
     * @throws GuzzleException
     */
    public function removeTarget(string $type, ?LoadBalancerTargetIp $ip = null, ?array $labelSelector = null, ?Server $server = null): ?APIResponse
    {
        $payload = [
            'type' => $type,
        ];
        if ($ip != null) {
            $payload['ip'] = $ip;
        }
        if (!empty($labelSelector)) {
            $payload['label_selector'] = $labelSelector;
        }
        if ($server != null) {
            $payload['server'] = $server;
        }
        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/remove_target'), [
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
     * Updates a Load Balancer Service.
     *
     * @see https://docs.hetzner.cloud/#load-balancer-actions-update-service
     * @throws APIException
     * @throws GuzzleException
     */
    public function updateService(int $destinationPort, LoadBalancerHealthCheck $healthCheck, int $listenPort, string $protocol, bool $proxyprotocol, ?LoadBalancerServiceHttp $http = null): ?APIResponse
    {
        $payload = [
            'destination_port' => $destinationPort,
            'health_check'     => $healthCheck,
            'listen_port'      => $listenPort,
            'protocol'         => $protocol,
            'proxyprotocol'    => $proxyprotocol,
        ];
        if ($http != null) {
            $payload['http'] = $http;
        }

        $response = $this->httpClient->post($this->replaceServerIdInUri('load_balancers/{id}/actions/update_service'), [
            'json' => $payload,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }
}
