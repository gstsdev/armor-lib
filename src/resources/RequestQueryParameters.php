<?php

namespace Armor\HandlingTools;

use ArrayAccess;
use Exception;

class RequestQueryParameters implements ArrayAccess {
    private $query_array;

    public function __construct($query_params_array)
    {
        $this->query_array = $query_params_array;
    }

    public function __get($param) {
        if (in_array($param, array_keys($this->query_array)))
            return $this->query_array[$param];
        
        return null;
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetExists($offset)
    {
        return isset($this->query_array[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception("Query parameters (for now) are read-only");
    }

    public function offsetUnset($offset)
    {
        unset($this->query_array[$offset]);
    }

    public function getParam($param, $converter=null) {
        $param = $this->__get($param);

        if ($converter)
            $param = call_user_func($converter, $param);
        
        return $param;
    }
}