<?php

namespace LKDev\HetznerCloud\Models\Actions;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\Contracts\Resources;
use LKDev\HetznerCloud\Models\Meta;
use LKDev\HetznerCloud\Models\Model;
use LKDev\HetznerCloud\RequestOpts;
use LKDev\HetznerCloud\Traits\GetFunctionTrait;
use stdClass;

class Actions extends Model implements Resources
{
    use GetFunctionTrait;

    protected array $actions;

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function all(?RequestOpts $requestOpts = null): array
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }

        return $this->_all($requestOpts);
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function list(?RequestOpts $requestOpts = null): ?APIResponse
    {
        if ($requestOpts == null) {
            $requestOpts = new RequestOpts();
        }
        $response = $this->httpClient->get('actions' . $requestOpts->buildQuery());
        if (!HetznerAPIClient::hasError($response)) {
            $resp = json_decode((string)$response->getBody());

            return APIResponse::create([
                'meta'    => Meta::parse($resp->meta),
                'actions' => self::parse($resp->{$this->_getKeys()['many']})->{$this->_getKeys()['many']},
            ], $response->getHeaders());
        }

        return null;
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function getById(int $id): ?Action
    {
        $response = $this->httpClient->get('actions/' . $id);
        if (!HetznerAPIClient::hasError($response)) {
            return Action::parse(json_decode((string)$response->getBody())->action);
        }

        return null;
    }

    public function getByName(string $name)
    {
        throw new BadMethodCallException('getByName is not possible on Actions');
    }

    public function setAdditionalData($input): static
    {
        $this->actions = collect($input)->map(function ($action) {
            return Action::parse($action);
        })->toArray();

        return $this;
    }

    public static function parse(stdClass|array|null $input): null|static
    {
        return (new self())->setAdditionalData($input);
    }

    /**
     * Wait for an action to complete.
     * @throws GuzzleException|APIException
     */
    public static function waitActionCompleted(Action $action, float $pollingIntervalInSeconds = 0.5): bool
    {
        while ($action->status == 'running') {
            usleep($pollingIntervalInSeconds * 1000000);
            $action = $action->reload();
        }

        return $action->status == 'success';
    }

    public function _getKeys(): array
    {
        return ['one'  => 'action',
                'many' => 'actions'
        ];
    }
}
