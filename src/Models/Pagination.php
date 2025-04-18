<?php

namespace LKDev\HetznerCloud\Models;

// This is a read only model, that does not have any logic. Just a stupid dataholder.
class Pagination extends Model
{
    public function __construct(
        public int $page,
        public int $per_page,
        public ?int $previous_page,
        public ?int $next_page,
        public int $last_page,
        public int $total_entries)
    {
        // Force getting the default http client
        parent::__construct();
    }

    public static function parse($input): null|static
    {
        if ($input == null) {
            return null;
        }

        return new self(
            page: $input->page,
            per_page: $input->per_page,
            previous_page: $input->previous_page,
            next_page: $input->next_page,
            last_page: $input->last_page,
            total_entries: $input->total_entries);
    }
}
