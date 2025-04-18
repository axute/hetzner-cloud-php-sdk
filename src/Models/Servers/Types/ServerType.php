<?php

namespace LKDev\HetznerCloud\Models\Servers\Types;

use LKDev\HetznerCloud\Models\Model;
use stdClass;

class ServerType extends Model
{

    public string $description;
    public string $cores;

    public string $memory;

    public string $disk;

    public array $prices;

    public string $storageType;

    public string $cpuType;
    public string $architecture;

    public function __construct(
        public int    $id,
        public string $name = '')
    {
        parent::__construct();
    }

    public function setAdditionalData($input): static
    {
        $this->name = $input->name;
        $this->description = $input->description;
        $this->cores = $input->cores;
        $this->memory = $input->memory;
        $this->disk = $input->disk;
        $this->prices = $input->prices;
        $this->storageType = $input->storage_type;
        $this->cpuType = $input->cpu_type;
        $this->architecture = $input->architecture;

        return $this;
    }

    public static function parse(stdClass|null|array $input): null|static
    {
        if ($input === null) {
            return null;
        }
        return (new self($input->id))->setAdditionalData($input);
    }
}
