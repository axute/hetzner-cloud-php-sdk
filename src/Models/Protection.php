<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 01.04.18
 * Time: 19:02.
 */

namespace LKDev\HetznerCloud\Models;

// This is a read only model, that does not have any logic. Just a stupid dataholder.
use stdClass;

class Protection extends Model
{

    public function __construct(public bool $delete = false, public bool $rebuild = false)
    {
        // Force getting the default http client
        parent::__construct();
    }

    public static function parse(null|array|stdClass $input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self($input->delete ?: false, $input->rebuild ?? false);
    }
}
