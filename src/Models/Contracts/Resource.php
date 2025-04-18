<?php

namespace LKDev\HetznerCloud\Models\Contracts;

use LKDev\HetznerCloud\APIResponse;

interface Resource
{
    public function reload();

    public function delete(): APIResponse|bool|null;

    public function update(array $data);
}
