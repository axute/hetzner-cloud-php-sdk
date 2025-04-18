<?php

namespace LKDev\HetznerCloud\Models\Networks;

use LKDev\HetznerCloud\RequestOpts;

/**
 * Class ServerRequestOpts.
 */
class NetworkRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        $this->name = $name;
        parent::__construct($perPage, $page, $labelSelector);
    }
}
