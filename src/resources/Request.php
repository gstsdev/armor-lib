<?php

namespace Armor\HandlingTools;

require "RequestQueryParameters.php";
require "RequestPath.php";

class Request {
    public $path, $query;
    private $method;

    public function __construct($method, $path, $path_params=array(), $query_params=array())
    {
        $this->method = $method;
        $this->path = new RequestPath($path, $path_params);
        $this->query = new RequestQueryParameters($query_params);
    }
}