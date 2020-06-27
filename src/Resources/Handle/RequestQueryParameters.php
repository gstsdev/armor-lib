<?php

namespace Armor\Handle;

use ArrayAccess;
use Exception;

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
        throw new Exception("Query parameters (for now) are read-only");
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