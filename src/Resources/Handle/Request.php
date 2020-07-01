<?php

namespace Armor\Handle;

// require_once __DIR__."/../../vendor/autoload.php";

use \Armor\Handle\RequestPath;
use \Armor\Handle\RequestQueryParameters;
use \Armor\Handle\Route;

use Exception;

/**
 * The representation of the request made to the application.
 * All information stored in this class is passed
 * by the `Router` class.
 * 
 * @property RequestQueryParameters query
 * @property RequestQueryParameters body
 */
class Request {
    /**
     * @var RequestPath $path
     * @var string $method
     */
    public $path, $method;
    private $_query;

    public function __construct($method, $path, $pathParameters=array(), $queryParameters=array())
    {
        $this->method = $method;
        $this->path = new RequestPath($path, $pathParameters);
        $this->_query = new RequestQueryParameters($queryParameters);
    }

    public function __get($name)
    {
        if ($name == 'query') {
            if ($this->method == 'get')
                return $this->_query;
            else
                throw new Exception('Method doesn\'t have query parameters');
        } elseif ($name == 'body') {
            if ($this->method == 'post')
                return $this->_query;
            else
                throw new Exception('Method doesn\'t have a request body');
        }
    }

    /**
     * Inject the value of each path parameter from the route object.
     * It's currently used by the `Router` class.
     * 
     * Behind the scenes, this method creates a new `RequestPath` object.
     * 
     * @param Route &$route
     */
    public function injectCustomParametersFromRoute(Route &$route) {
        $this->path = new RequestPath($this->path->absolute, $route->getParsedRouteParameters());
    }
}