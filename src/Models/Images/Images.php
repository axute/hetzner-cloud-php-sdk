<?php

namespace LKDev\HetznerCloud\Models\Images;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;

class Images extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $images;

    /**
     * Returns all image objects.
     * @see https://docs.hetzner.cloud/#resources-images-get
     * @throws GuzzleException|APIException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new ImageRequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * Returns all image objects.
     * @see https://docs.hetzner.cloud/#resources-images-get
     * @throws APIException
     * @throws GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new ImageRequestOpts();
        }
        $response = $this->httpClient->get('images' . $requestOpts->buildQuery());
        if (!HetznerAPIClient::hasError($response)) {
            $resp = json_decode((string)$response->getBody());

            return APIResponse::create([
                'meta'                    => Meta::parse($resp->meta),
                $this->_getKeys()['many'] => self::parse($resp->{$this->_getKeys()['many']})->{$this->_getKeys()['many']},
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Returns a specific image object.
     * @see https://docs.hetzner.cloud/#resources-images-get-1
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?Image
    {
        $response = $this->httpClient->get('images/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return Image::parse(json_decode((string)$response->getBody())->image);
        }

        return null;
    }

    /**
     * Returns a specific image object by its name.
     * @see https://docs.hetzner.cloud/#resources-images-get-1
     * @throws APIException|GuzzleException
     */
    public function getByName(string $name, ?string $architecture = null): ?Image
    {
        /** @var Images $images */
        $images = $this->list(new ImageRequestOpts($name, null, null, null, $architecture));

        return (count($images->images) > 0) ? $images->images[0] : null;
    }

    public function setAdditionalData($input): static
    {
        $this->images = collect($input)->map(function ($image) {
            return Image::parse($image);
        })->toArray();

        return $this;
    }

    public static function parse($input): static
    {
        return (new self())->setAdditionalData($input);
    }

    public function _getKeys(): array
    {
        return ['one'  => 'image',
                'many' => 'images'
        ];
    }
}
