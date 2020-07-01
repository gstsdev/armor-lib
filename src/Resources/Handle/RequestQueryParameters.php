<?php

namespace Armor\Handle;

use ArrayAccess;
use Exception;

/**
 * The representation of the query parameters passed to
 * the request made to the application.
 */
class RequestQueryParameters implements ArrayAccess {
    private $queryArray;

    public function __construct($queryParametersArray)
    {
        $this->queryArray = $queryParametersArray;
    }

    public function __get($param) {
        if (in_array($param, array_keys($this->queryArray)))
            return $this->queryArray[$param];
        
        return null;
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetExists($offset)
    {
        return isset($this->queryArray[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        // When I say "for now", I say thinking of the possibility
        // to change this query parameters object and redirect the user
        // to an "updated page". But, as I said, it's just a "thinking" 
        throw new Exception("Query parameters are (for now) read-only");
    }

    public function offsetUnset($offset)
    {
        unset($this->queryArray[$offset]);
    }

    public function getParam($param, $converter=null) {
        $param = $this->__get($param);

        if ($converter)
            $param = call_user_func($converter, $param);
        
        return $param;
    }
}