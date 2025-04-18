<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use LKDev\HetznerCloud\RequestOpts;

class LoadBalancerRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
