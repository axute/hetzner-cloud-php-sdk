<?php

namespace LKDev\HetznerCloud\Models\Actions;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resource;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\Traits\PropertiesTrait;
use stdClass;


class Action extends Model implements Resource
{
    use PropertiesTrait;

    public function __construct(
        public int                  $id,
        public string               $command,
        public int                  $progress,
        public string               $status,
        public string               $started,
        public ?string              $finished = null,
        public ?array               $resources = null,
        public string|stdClass|null $error = null
    )
    {
        parent::__construct();
    }

    /**
     * Wait for an action to complete.
     * @throws GuzzleException|APIException
     */
    public function waitUntilCompleted(float $pollingIntervalInSeconds = 0.5): bool
    {
        return Actions::waitActionCompleted($this, $pollingIntervalInSeconds);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function reload(): ?static
    {
        return HetznerAPIClient::$instance->actions()->getById($this->id);
    }

    public function delete(): APIResponse|bool|null
    {
        throw new BadMethodCallException('delete on action is not possible');
    }

    public function update(array $data)
    {
        throw new BadMethodCallException('update on action is not possible');
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(id: $input->id,
            command: $input->command,
            progress: $input->progress,
            status: $input->status,
            started: $input->started,
            finished: $input->finished,
            resources: $input->resources,
            error: $input->error ?? null);
    }
}
