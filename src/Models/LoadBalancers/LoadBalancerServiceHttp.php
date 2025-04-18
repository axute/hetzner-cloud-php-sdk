<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use LKDev\HetznerCloud\Models\Model;

class LoadBalancerServiceHttp extends Model
{
    public function __construct(
        public array  $certificates,
        public int    $cookie_lifetime,
        public string $cookie_name,
        public bool   $redirect_http,
        public bool   $sticky_sessions)
    {
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self($input->certificates, $input->cookie_lifetime, $input->cookie_name, $input->redirect_http, $input->sticky_essions);
    }
}
