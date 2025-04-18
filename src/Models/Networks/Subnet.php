<?php /** @noinspection PhpUnused */

namespace LKDev\HetznerCloud\Models\Networks;

use GuzzleHttp\Client;
use LKDev\HetznerCloud\Models\Model;

/**
 * Class Subnet.
 */
class Subnet extends Model
{
    const string TYPE_SERVER = 'server';
    const string TYPE_CLOUD = 'cloud';

    public function __construct(
        public string  $type,
        public string  $ipRange,
        public string  $network_zone,
        public ?string $gateway = null,
        ?Client        $client = null)
    {
        parent::__construct($client);
    }

    public static function parse($input, ?Client $client = null): array
    {
        return collect($input)->map(function ($subnet) use ($client) {
            return new self($subnet->type, $subnet->ip_range, $subnet->network_zone, $subnet->gateway, $client);
        })->toArray();
    }

    public function __toRequestPayload(): array
    {
        return [
            'type'         => $this->type,
            'ip_range'     => $this->ipRange,
            'network_zone' => $this->network_zone,
        ];
    }
}
