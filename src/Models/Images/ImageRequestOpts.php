<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 2019-03-28
 * Time: 13:51.
 */

namespace LKDev\HetznerCloud\Models\Images;

use LKDev\HetznerCloud\RequestOpts;

class ImageRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null, public ?string $architecture = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
