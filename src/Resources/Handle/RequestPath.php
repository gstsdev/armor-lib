<?php

namespace Armor\Handle;

use ArrayAccess;
use Exception;

/**
 * The representation of the path requested to application.
 * This class is also responsible for storing the "route/path parameters",
 * which were parsed on the `Route` object.
 * 
 * @param \string $absolutePath The actual path requested to the application
 * @param \array $pathPlaceholders The route/path parameters
 */
class RequestPath implements ArrayAccess {
    /** 
     * The actual path requested to the application.
     * 
     * @var \string 
     */
    public $absolute;
    /** 
     * The route/path parameters.
     * 
     * @var \array 
     */
    private $placeholders;

    public function __construct(string $absolutePath, array $pathPlaceholders)
    {
        $this->absolute = $absolutePath;
        $this->placeholders = $pathPlaceholders;
    }

    /** @ignore */
    public function __get($param) {
        if (in_array($param, array_keys($this->placeholders)))
            return $this->placeholders[$param];
        
        return null;
    }

    /** @ignore */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /** @ignore */
    public function offsetExists($offset)
    {
        return isset($this->placeholders[$offset]);
    }

    /** @ignore */
    public function offsetSet($offset, $value)
    {
        throw new Exception("Path parameters are read-only");
    }

    /** @ignore */
    public function offsetUnset($offset)
    {
        unset($this->placeholders[$offset]);
    }
}