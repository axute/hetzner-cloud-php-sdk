<?php

namespace LKDev\HetznerCloud\Models\PlacementGroups;

use LKDev\HetznerCloud\RequestOpts;

/**
 * Class ServerRequestOpts.
 */
class PlacementGroupRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, public ?string $type = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
