<?php

namespace LKDev\HetznerCloud\Traits;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\RequestOpts;

trait GetFunctionTrait
{
    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function get($nameOrId): mixed
    {
        try {
            return $this->getById((int)$nameOrId);
        } catch (Exception $e) {
            unset($e);

            return $this->getByName($nameOrId);
        }
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    protected function _all(RequestOpts $requestOpts): array
    {
        $entities = [];
        $requestOpts->per_page = HetznerAPIClient::MAX_ENTITIES_PER_PAGE;
        $max_pages = PHP_INT_MAX;
        for ($i = 1; $i < $max_pages; $i++) {
            $requestOpts->page = $i;
            /** @var object $_f */
            $_f = $this->list($requestOpts);
            $entities = array_merge($entities, $_f->{$this->_getKeys()['many']});
            if ($_f->meta->pagination->page === $_f->meta->pagination->last_page || $_f->meta->pagination->last_page === null) {
                $max_pages = 0;
            }
        }

        return $entities;
    }
}
