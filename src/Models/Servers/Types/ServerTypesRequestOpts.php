<?php

namespace LKDev\HetznerCloud\Models\Servers\Types;

use LKDev\HetznerCloud\RequestOpts;

/**
 * Class ServerRequestOpts.
 */
class ServerTypesRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
