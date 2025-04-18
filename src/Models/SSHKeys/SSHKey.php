<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 21:00.
 */

namespace LKDev\HetznerCloud\Models\SSHKeys;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Model;

class SSHKey extends Model implements Resource
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $fingerprint,
        public string $public_key,
        public array  $labels = [])
    {
        parent::__construct();
    }

    /**
     * Update a ssh key.
     * @see https://docs.hetzner.cloud/#resources-ssh-keys-put
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?self
    {
        $response = $this->httpClient->put('ssh_keys/' . $this->id, [
            'json' => $data,

        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return self::parse(json_decode((string)$response->getBody())->ssh_key);
        }

        return null;
    }

    /**
     * Deletes a SSH key. It cannot be used anymore.
     * @see https://docs.hetzner.cloud/#resources-ssh-keys-delete
     * @throws APIException|GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('ssh_keys/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return true;
        }

        return false;
    }

    public static function parse($input): null|static
    {
        return new self($input->id, $input->name, $input->fingerprint, $input->public_key, get_object_vars($input->labels));
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->sshKeys()->get($this->id);
    }
}
