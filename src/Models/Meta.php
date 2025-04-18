<?php

namespace LKDev\HetznerCloud\Models;

// This is a read only model, that does not have any logic. Just a stupid dataholder.

/**
 * Class Meta.
 */
class Meta extends Model
{
    public function __construct(public Pagination $pagination)
    {
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(Pagination::parse($input->pagination));
    }
}
