<?php

namespace LKDev\HetznerCloud\Models\Firewalls;

use LKDev\HetznerCloud\RequestOpts;

/**
 * Class FirewallRequestOpts.
 */
class FirewallRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        $this->name = $name;
        parent::__construct($perPage, $page, $labelSelector);
    }
}
