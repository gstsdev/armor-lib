<?php

namespace Armor\HandlingTools;

use ArrayAccess;

class RequestPath implements ArrayAccess {
    public $absolute;
    private $placeholders;

    public function __construct(string $absolute_path, array $path_placeholders)
    {
        $this->absolute = $absolute_path;
        $this->placeholders = $path_placeholders;
    }

    public function __get($param) {
        if (in_array($param, array_keys($this->placeholders)))
            return $this->placeholders[$param];
        
        return null;
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetExists($offset)
    {
        return isset($this->placeholders[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception("Path parameters are read-only");
    }

    public function offsetUnset($offset)
    {
        unset($this->placeholders[$offset]);
    }
}