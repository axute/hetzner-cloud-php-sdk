<?php

namespace LKDev\HetznerCloud\Models;

// This is a read only model, that does not have any logic. Just a stupid dataholder.

/**
 * Class Meta.
 */
class Meta extends Model
{
    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * Meta constructor.
     *
     * @param  Pagination  $pagination
     */
    public function __construct(Pagination $pagination)
    {
        $this->pagination = $pagination;
        parent::__construct(null);
    }

    /**
     * @param  $input
     * @return Meta|null|static
     */
    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(Pagination::parse($input->pagination));
    }
}
