<?php

namespace Armor\Handle;

use ArrayAccess;
use Exception;

class RequestPath implements ArrayAccess {
    public $absolute;
    private $placeholders;

    public function __construct(string $absolutePath, array $pathPlaceholders)
    {
        $this->absolute = $absolutePath;
        $this->placeholders = $pathPlaceholders;
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