<?php

namespace Armor\Handle;

use ArrayAccess;
use Exception;

/**
 * The representation of the path requested to application.
 * This class is also responsible for storing the "path parameters",
 * which were parsed on the `Route` object.
 */
class RequestPath implements ArrayAccess {
    /** @var string */
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