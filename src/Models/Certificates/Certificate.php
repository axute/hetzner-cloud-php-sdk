<?php

namespace LKDev\HetznerCloud\Models\Certificates;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Model;
use stdClass;

class Certificate extends Model implements Resource
{
    public function __construct(
        public int                 $id,
        public ?string             $name = null,
        public ?string             $certificate = null,
        public ?string             $created = null,
        public ?string             $not_valid_before = null,
        public ?string             $not_valid_after = null,
        public ?array              $domain_names = null,
        public ?string             $fingerprint = null,
        public ?array              $used_by = null,
        public stdClass|array|null $labels = [])
    {
        parent::__construct();
    }

    /**
     * Update a ssh key.
     *
     * @see https://docs.hetzner.cloud/#resources-certificates-put
     *
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?static
    {
        $response = $this->httpClient->put('certificates/' . $this->id, [
            'json' => $data,

        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return self::parse(json_decode((string)$response->getBody())->certificate);
        }

        return null;
    }

    /**
     * Deletes an SSH key. It cannot be used anymore.
     *
     * @see https://docs.hetzner.cloud/#resources-certificates-delete
     *
     * @throws APIException|GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('certificates/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return true;
        }

        return false;
    }

    public static function parse($input): null|static
    {
        return new self(id: $input->id,
            name: $input->name,
            certificate: $input->certificate,
            created: $input->created,
            not_valid_before: $input->not_valid_before,
            not_valid_after: $input->not_valid_after,
            domain_names: $input->domain_names,
            fingerprint: $input->fingerprint,
            used_by: $input->used_by,
            labels: $input->labels);
    }

    /**
     * Reload the data of the SSH Key.
     * @throws GuzzleException|APIException
     */
    public function reload(): Certificate
    {
        return HetznerAPIClient::$instance->certificates()->get($this->id);
    }
}
