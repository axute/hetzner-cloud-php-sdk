<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 21:00.
 */

namespace LKDev\HetznerCloud\Models\Locations;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resource;

class Location extends LocationReference implements Resource
{
    public function __construct(
        int     $id,
        string  $name,
        public ?string $description = null,
        public ?string $country = null,
        public ?string $city = null,
        public ?float  $latitude = null,
        public ?float  $longitude = null,
        public ?string $network_zone = null
    )
    {
        parent::__construct(id: $id, name: $name);
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }
        $network_zone = property_exists($input, 'network_zone') ? $input->network_zone : null;

        return new self(
            id: $input->id,
            name: $input->name,
            description: $input->description,
            country: $input->country,
            city: $input->city,
            latitude: $input->latitude,
            longitude: $input->longitude,
            network_zone: $network_zone
        );
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->locations()->get($this->id);
    }

    public function delete(): APIResponse|bool|null
    {
        throw new BadMethodCallException('delete on location is not possible');
    }

    public function update(array $data)
    {
        throw new BadMethodCallException('update on location is not possible');
    }
}
