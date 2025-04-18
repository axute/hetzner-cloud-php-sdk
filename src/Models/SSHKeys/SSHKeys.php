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
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class SSHKeys extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $ssh_keys;

    /**
     * Creates a new SSH Key with the given name and public_key.
     * @see https://docs.hetzner.cloud/#resources-ssh-keys-post
     * @throws APIException|GuzzleException
     */
    public function create(
        string $name,
        string $publicKey,
        array $labels = []
    ): ?SSHKey {
        $parameters = [
            'name' => $name,
            'public_key' => $publicKey,
        ];
        if (! empty($labels)) {
            $parameters['labels'] = $labels;
        }
        $response = $this->httpClient->post('ssh_keys', [
            'json' => $parameters,
        ]);
        if (! HetznerAPIClient::hasError($response)) {
            return SSHKey::parse(json_decode((string) $response->getBody())->ssh_key);
        }

        return null;
    }

    /**
     * Returns all ssh key objects.
     * @see https://docs.hetzner.cloud/#resources-ssh-keys-get
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all ssh key objects.
     * @see https://docs.hetzner.cloud/#resources-ssh-keys-get
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }
        $response = $this->httpClient->get('ssh_keys'.$requestOpts->buildQuery());
        if (! HetznerAPIClient::hasError($response)) {
            $resp = json_decode((string) $response->getBody());

            return APIResponse::create([
                'meta' => Meta::parse($resp->meta),
                $this->_getKeys()['many'] => self::parse($resp->{$this->_getKeys()['many']})->{$this->_getKeys()['many']},
            ], $response->getHeaders());
        }

        return null;
    }

    public function setAdditionalData($input):static
    {
        $this->ssh_keys = collect($input)->map(function ($sshKey) {
            return SSHKey::parse($sshKey);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    /**
     * Returns a specific ssh key object.
     * @see https://docs.hetzner.cloud/#resources-ssh-keys-get-1
     * @throws APIException|GuzzleException
     */
    public function getById(int $id):SSHKey|null
    {
        $response = $this->httpClient->get('ssh_keys/'.$id);
        if (! HetznerAPIClient::hasError($response)) {
            return SSHKey::parse(json_decode((string) $response->getBody())->ssh_key);
        }

        return null;
    }

    /**
     * Returns a specific ssh key object.
     * @see https://docs.hetzner.cloud/#resources-ssh-keys-get-1
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name): ?SSHKey
    {
        /** @var SSHKeys $sshKeys */
        $sshKeys = $this->list(new SSHKeyRequestOpts($name));

        return (count($sshKeys->ssh_keys) > 0) ? $sshKeys->ssh_keys[0] : null;
    }

    public function _getKeys(): array
    {
        return ['one' => 'ssh_key', 'many' => 'ssh_keys'];
    }
}
