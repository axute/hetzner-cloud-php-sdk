<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 21:01.
 */

namespace LKDev\HetznerCloud\Models\Datacenters;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Locations\Location;
use LKDev\HetznerCloud\Models\Model;
use stdClass;

class Datacenter extends Model implements Resource
{
    public function __construct(
        public int            $id,
        public string         $name,
        public string         $description,
        public Location       $location,
        public stdClass|array $server_types = []
    )
    {
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(id: $input->id,
            name: $input->name,
            description: $input->description,
            location: Location::parse($input->location),
            server_types: $input->server_types);
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->datacenters()->get($this->id);
    }

    public function delete(): APIResponse|bool|null
    {
        throw new BadMethodCallException('delete on datacenter is not possible');
    }

    public function update(array $data)
    {
        throw new BadMethodCallException('update on datacenter is not possible');
    }
}
