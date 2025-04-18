<?php

namespace LKDev\HetznerCloud\Models\FloatingIps;

use LKDev\HetznerCloud\RequestOpts;

/**
 * Class FloatingIPRequestOpts.
 */
class FloatingIPRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
