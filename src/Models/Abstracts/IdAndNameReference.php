<?php

namespace LKDev\HetznerCloud\Models\Abstracts;

use GuzzleHttp\Client;
use LKDev\HetznerCloud\Clients\GuzzleClient;
use stdClass;

abstract class IdAndNameReference extends IdReference
{
    public function __construct(?int $id = null, public ?string $name = null, Client|GuzzleClient|null $httpClient = null)
    {
        parent::__construct($id, $httpClient);
    }

    public static function parse(array|stdClass|null $input): null|static
    {
        if ($input === null) {
            return null;
        }
        return new static(id: $input->id ?? null, name: $input->name ?? null);
    }
}