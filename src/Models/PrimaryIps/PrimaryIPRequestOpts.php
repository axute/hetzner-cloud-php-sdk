<?php

namespace LKDev\HetznerCloud\Models\PrimaryIps;

use LKDev\HetznerCloud\RequestOpts;

/**
 * Class PrimaryIPRequestOpts.
 */
class PrimaryIPRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
