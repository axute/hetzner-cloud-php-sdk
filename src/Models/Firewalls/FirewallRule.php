<?php

namespace LKDev\HetznerCloud\Models\Firewalls;

class FirewallRule
{
    const string DIRECTION_IN = 'in';

    const string DIRECTION_OUT = 'out';

    const string PROTOCOL_TCP = 'tcp';

    const string PROTOCOL_UDP = 'udp';

    const string PROTOCOL_ICMP = 'icmp';

    /**
     * FirewallRule constructor.
     * @param string[] $source_ips
     * @param string[] $destination_ips
     */
    public function __construct(
        public string  $direction,
        public string  $protocol,
        public array   $source_ips = [],
        public array   $destination_ips = [],
        public ?string $port = '',
        public ?string $description = null)
    {
    }

    public function toRequestSchema(): array
    {
        $s = [
            'direction'  => $this->direction,
            'source_ips' => $this->source_ips,
            'protocol'   => $this->protocol,
        ];
        if (!empty($this->destination_ips)) {
            $s['destination_ips'] = $this->destination_ips;
        }
        if ($this->port != '') {
            $s['port'] = $this->port;
        }

        return $s;
    }
}
