<?php

namespace LKDev\HetznerCloud\Models\Firewalls;

/**
 * Class FirewallRule.
 */
class FirewallRule
{
    const string DIRECTION_IN = 'in';

    const string DIRECTION_OUT = 'out';

    const string PROTOCOL_TCP = 'tcp';

    const string PROTOCOL_UDP = 'udp';

    const string PROTOCOL_ICMP = 'icmp';
    /**
     * @var string
     */
    public string $direction;
    /**
     * @var array<string>
     */
    public array $sourceIPs;
    /**
     * @var array<string>
     */
    public array $destinationIPs;
    /**
     * @var string
     */
    public string $protocol;
    /**
     * @var string|null
     */
    public ?string $port;
    public ?string $description;

    /**
     * FirewallRule constructor.
     *
     * @param string $direction
     * @param string $protocol
     * @param string[] $sourceIPs
     * @param string[] $destinationIPs
     * @param string|null $port
     * @param string|null $description
     */
    public function __construct(string $direction, string $protocol, array $sourceIPs = [], array $destinationIPs = [], ?string $port = '', ?string $description = null)
    {
        $this->direction = $direction;
        $this->sourceIPs = $sourceIPs;
        $this->destinationIPs = $destinationIPs;
        $this->protocol = $protocol;
        $this->port = $port;
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function toRequestSchema(): array
    {
        $s = [
            'direction' => $this->direction,
            'source_ips' => $this->sourceIPs,
            'protocol' => $this->protocol,
        ];
        if (! empty($this->destinationIPs)) {
            $s['destination_ips'] = $this->destinationIPs;
        }
        if ($this->port != '') {
            $s['port'] = $this->port;
        }

        return $s;
    }
}
