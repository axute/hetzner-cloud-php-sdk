<?php

namespace LKDev\HetznerCloud\Models\Abstracts;

use GuzzleHttp\Client;
use LKDev\HetznerCloud\Clients\GuzzleClient;
use LKDev\HetznerCloud\Models\Model;
use stdClass;

abstract class IdReference extends Model
{
    public function __construct(public ?int $id = null, Client|GuzzleClient|null $httpClient = null)
    {
        parent::__construct($httpClient);
    }

    public static function parse(array|stdClass|null $input): null|static|array
    {
        return new static(id: $input->id);
    }

}