<?php

namespace LKDev\HetznerCloud\Models\Servers;

use LKDev\HetznerCloud\RequestOpts;

/**
 * Class ServerRequestOpts.
 */
class ServerRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, public ?string $status = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
