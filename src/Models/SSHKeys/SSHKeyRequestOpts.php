<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 2019-03-28
 * Time: 13:51.
 */

namespace LKDev\HetznerCloud\Models\SSHKeys;

use LKDev\HetznerCloud\RequestOpts;

class SSHKeyRequestOpts extends RequestOpts
{
    public function __construct(public ?string $name = null, public ?string $fingerprint = null, ?int $perPage = null, ?int $page = null, ?string $labelSelector = null)
    {
        parent::__construct($perPage, $page, $labelSelector);
    }
}
