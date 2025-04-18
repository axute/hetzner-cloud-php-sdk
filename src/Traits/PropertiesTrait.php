<?php

namespace LKDev\HetznerCloud\Traits;

trait PropertiesTrait
{
    protected array $_properties = [];

    public function __get($name)
    {
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->_properties[$name] = $value;
    }
}