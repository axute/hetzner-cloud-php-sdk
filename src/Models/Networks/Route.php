<?php

namespace LKDev\HetznerCloud\Models\Networks;

use GuzzleHttp\Client;
use LKDev\HetznerCloud\Models\Model;

/**
 * Class Route.
 */
class Route extends Model
{

    public function __construct(
        public string $destination,
        public string $gateway,
        ?Client       $client = null)
    {
        parent::__construct($client);
    }

    public static function parse($input, ?Client $client = null): array
    {
        return collect($input)->map(function ($route) use ($client) {
            return new self($route->destination, $route->gateway, $client);
        })->toArray();
    }

    public function __toRequestPayload(): array
    {
        return [
            'destination' => $this->destination,
            'gateway'     => $this->gateway,
        ];
    }
}
