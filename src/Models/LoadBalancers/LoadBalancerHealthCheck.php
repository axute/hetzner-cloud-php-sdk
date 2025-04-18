<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use LKDev\HetznerCloud\Models\Model;

class LoadBalancerHealthCheck extends Model
{
    public function __construct(
        public LoadBalancerHealthCheckHttp $http,
        public int                         $interval,
        public int                         $port,
        public string                      $protocol,
        public int                         $retries,
        public int                         $timeout)
    {
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(LoadBalancerHealthCheckHttp::parse($input->http), $input->interval, $input->port, $input->protocol, $input->retries, $input->timeout);
    }
}
