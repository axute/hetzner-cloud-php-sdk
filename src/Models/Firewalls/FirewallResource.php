<?php

namespace LKDev\HetznerCloud\Models\Firewalls;

use LKDev\HetznerCloud\Models\Servers\ServerReference;

/**
 * Class FirewallResource.
 */
class FirewallResource
{
    const string TYPE_SERVER = 'server';

    public function __construct(
        public string  $type,
        public ?ServerReference $server)
    {
    }

    /**
     * @return string[]
     */
    public function toRequestSchema(): array
    {
        $s = ['type' => $this->type];
        if ($this->type == self::TYPE_SERVER) {
            $s['server'] = ['id' => $this->server->id];
        }

        return $s;
    }
}
