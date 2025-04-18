<?php /** @noinspection PhpUnused */

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 28.01.18
 * Time: 21:01.
 */

namespace LKDev\HetznerCloud\Models\Images;

use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Actions\Action;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Protection;
use LKDev\HetznerCloud\Models\Servers\ServerReference;

class Image extends ImageReference implements Resource
{
    const string TYPE_SYSTEM = 'system';
    const string TYPE_SNAPSHOT = 'snapshot';
    const string TYPE_BACKUP = 'backup';

    public function __construct(
        int                    $id,
        public string          $type,
        public string          $status,
        ?string                $name,
        public string          $description,
        public ?float          $image_size,
        public int             $disk_size,
        public string          $created,
        public ServerReference $created_from,
        public string          $os_flavor,
        public ?int            $bound_to = null,
        public ?string         $os_version = null,
        public bool            $rapid_deploy = false,
        public Protection      $protection = new Protection(false),
        public ?string         $architecture = null,
        public array           $labels = []
    )
    {
        parent::__construct($id, $name);
    }

    /**
     * Updates the Image. You may change the description or convert a Backup image to a Snapshot Image. Only images of type snapshot and backup can be updated.
     *
     * @see https://docs.hetzner.cloud/#resources-images-put
     *
     *
     * @throws APIException|GuzzleException
     */
    public function update(array $data): ?self
    {
        $response = $this->httpClient->put('images/' . $this->id, [
            'json' => $data,
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return self::parse(json_decode((string)$response->getBody())->image);
        }

        return null;
    }

    /**
     * Changes the protection configuration of the image. Can only be used on snapshots.
     *
     * @see https://docs.hetzner.cloud/#image-actions-change-image-protection
     *
     * @throws APIException|GuzzleException
     */
    public function changeProtection(bool $delete = true): ?APIResponse
    {
        $response = $this->httpClient->post('images/' . $this->id . '/actions/change_protection', [
            'json' => [
                'delete' => $delete,
            ],
        ]);
        if (!HetznerAPIClient::hasError($response)) {
            return APIResponse::create([
                'action' => Action::parse(json_decode((string)$response->getBody())->action),
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * Deletes an Image. Only images of type snapshot and backup can be deleted.
     *
     * @see https://docs.hetzner.cloud/#resources-images-delete
     *
     * @throws APIException|GuzzleException
     */
    public function delete(): APIResponse|bool|null
    {
        $response = $this->httpClient->delete('images/' . $this->id);
        if (!HetznerAPIClient::hasError($response)) {
            return true;
        }

        return false;
    }

    public static function parse($input): ?static
    {
        if ($input == null) {
            return null;
        }

        return new self(
            id: $input->id,
            type: $input->type,
            status: $input->status ?: null,
            name: $input->name,
            description: $input->description,
            image_size: $input->image_size,
            disk_size: $input->disk_size,
            created: $input->created,
            created_from: new ServerReference($input->created_from->id),
            os_flavor: $input->os_flavor,
            bound_to: $input->bound_to,
            os_version: $input->os_version,
            rapid_deploy: $input->rapid_deploy,
            protection: Protection::parse($input->protection),
            architecture: $input->architecture,
            labels: get_object_vars($input->labels));
    }

    /**
     * @throws GuzzleException|APIException
     */
    public function reload(): mixed
    {
        return HetznerAPIClient::$instance->images()->get($this->id);
    }
}
