<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 2019-03-28
 * Time: 13:51.
 */

namespace LKDev\HetznerCloud\Models\Actions;

use LKDev\HetznerCloud\RequestOpts;

class ActionRequestOpts extends RequestOpts
{
    public function __construct(public ?string $status = null,public ?string $sort = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
