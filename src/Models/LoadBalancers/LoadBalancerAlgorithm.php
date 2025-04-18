<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use LKDev\HetznerCloud\Models\Model;

class LoadBalancerAlgorithm extends Model
{
    /**
     * @var string
     */
    public $type;

    /**
     * @param  string  $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
        parent::__construct();
    }

    /**
     * @param  $input
     * @return LoadBalancerAlgorithm|null|static
     */
    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self($input->type);
    }
}
