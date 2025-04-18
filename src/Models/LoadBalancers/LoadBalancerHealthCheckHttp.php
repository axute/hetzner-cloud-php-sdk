<?php

namespace LKDev\HetznerCloud\Models\LoadBalancers;

use LKDev\HetznerCloud\Models\Model;

class LoadBalancerHealthCheckHttp extends Model
{
    public function __construct(
        public ?string $domain,
        public string  $path,
        public string  $response,
        public array   $status_codes,
        public bool    $tls)
    {
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self($input->domain, $input->path, $input->response, $input->status_codes, $input->tls);
    }
}
