<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 2019-03-28
 * Time: 13:51.
 */

namespace LKDev\HetznerCloud\Models\Volumes;

use LKDev\HetznerCloud\RequestOpts;

class VolumeRequestOpts extends RequestOpts
{
    public ?string $name;

    public ?string $status;

    public function __construct(?string $name = null, ?string $status = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
        $this->name = $name;
        $this->status = $status;
    }
}
