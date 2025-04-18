<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use LKDev\HetznerCloud\Models\Model;

class LoadBalancerAlgorithm extends Model
{

    public function __construct(public string $type)
    {
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self($input->type);
    }
}
