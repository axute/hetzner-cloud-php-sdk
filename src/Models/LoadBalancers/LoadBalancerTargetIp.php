<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use LKDev\HetznerCloud\Models\Model;

class LoadBalancerTargetIp extends Model
{
    public function __construct(public string $ip)
    {
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self($input->ip);
    }
}
