<?php

/**
 * Created by PhpStorm.
 * User: lkaemmerling
 * Date: 05.09.18
 * Time: 10:55.
 */

namespace LKDev\HetznerCloud;

use InvalidArgumentException;

class RequestOpts
{
    public function __construct(public ?int $per_page = null, public ?int $page = null, public ?string $label_selector = null)
    {
        if ($this->per_page > HetznerAPIClient::MAX_ENTITIES_PER_PAGE) {
            throw new InvalidArgumentException('perPage can not be larger than ' . HetznerAPIClient::MAX_ENTITIES_PER_PAGE);
        }
    }

    public function buildQuery(): string
    {
        $values = collect(get_object_vars($this))
            ->filter(function ($var) {
                return $var != null;
            })->toArray();

        return count($values) == 0 ? '' : ('?' . http_build_query($values));
    }
}
