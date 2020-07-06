<?php

namespace Armor\Handle;

use ArrayAccess;
use Exception;

/**
 * The representation of the query parameters passed to
 * the request made to the application.
 * 
 * @param \array $queryParametersArray The associative array that represents the query parameters
 */
class RequestQueryParameters implements ArrayAccess {
    /**
     * The associative array that represents the query parameters.
     *  
     * @var \array 
     */
    private $queryArray;

    public function __construct($queryParametersArray)
    {
        $this->queryArray = $queryParametersArray;
    }

    /** @ignore */
    public function __get($param) {
        if (in_array($param, array_keys($this->queryArray)))
            return $this->queryArray[$param];
        
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
        return isset($this->queryArray[$offset]);
    }

    /** @ignore */
    public function offsetSet($offset, $value)
    {
        // When I say "for now", I say thinking of the possibility
        // to change this query parameters object and redirect the user
        // to an "updated page". But, as I said, it's just a "thinking" 
        throw new Exception("Query parameters are (for now) read-only");
    }

    /** @ignore */
    public function offsetUnset($offset)
    {
        unset($this->queryArray[$offset]);
    }

    /**
     * Returns the value of a query parameter. If the parameter
     * `$converter` is given, it's used to convert the value
     * of the query parameter.
     * 
     * @param \string $param
     * @param \callable $converter
     * @return \string|\object
     */
    public function getParam($param, $converter=null) {
        $param = $this->__get($param);

        if ($converter !== null && is_callable($converter))
            $param = call_user_func($converter, $param);
        
        return $param;
    }
}