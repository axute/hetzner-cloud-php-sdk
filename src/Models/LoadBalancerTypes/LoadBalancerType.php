<?php

namespace LKDev\HetznerCloud\Models\LoadBalancerTypes;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\Models\Prices\Prices;
use stdClass;

class LoadBalancerType extends Model implements Resource
{
    public function __construct(
        public int               $id,
        public string            $name,
        public ?string           $deprecated,
        public string            $description,
        public int               $max_assigned_certificates,
        public int               $max_connections,
        public int               $max_services,
        public int               $max_targets,
        public Prices|array|null $prices)
    {
        parent::__construct();
    }

    public static function parse(null|stdClass|array $input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(
            id: $input->id,
            name: $input->name,
            deprecated: $input->deprecated,
            description: $input->description,
            max_assigned_certificates: $input->max_assigned_certificates,
            max_connections: $input->max_connections,
            max_services: $input->max_services,
            max_targets: $input->max_targets,
            prices: Prices::parse($input->prices)
        );
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->loadBalancerTypes()->get($this->id);
    }

    public function delete(): ?APIResponse
    {
        throw new BadMethodCallException('delete on load balancer type is not possible');
    }

    public function update(array $data)
    {
        throw new BadMethodCallException('update on load balancer type is not possible');
    }
}
