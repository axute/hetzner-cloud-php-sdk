<?php

namespace LKDev\HetznerCloud\Models\ISOs;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resource;

class ISO extends ISOReference implements Resource
{
    public function __construct(
        int            $id,
        ?string        $name = null,
        public ?string $description = null,
        public ?string $type = null
    )
    {
        parent::__construct(id: $id, name: $name);
    }

    public static function parse($input): ?static
    {
        if ($input == null) {
            return null;
        }

        return new self(
            id: $input->id,
            name: $input->name,
            description: $input->description,
            type: $input->type);
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->isos()->get($this->id);
    }

    public function delete(): APIResponse|bool|null
    {
        throw new BadMethodCallException('delete on ISOs is not possible');
    }

    public function update(array $data)
    {
        throw new BadMethodCallException('update on ISOs is not possible');
    }
}
