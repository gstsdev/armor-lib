<?php

namespace Armor;

require_once __DIR__."/../vendor/autoload.php";
//require_once "resources/Exceptions/exceptions.php";
//require_once "resources/handlingtools.php";

use \Armor\Exceptions\ProhibitedMethodException;

use \Armor\Handle as Handle;

use ArgumentCountError;
use ArrayAccess;
use TypeError;

const ALLOWED_METHODS = array('get', 'post');

/**
 * Creates an Application instance, which
 * is responsible for setting the routes and
 * handling the requests.
 * 
 * @param Callable $encoder
 */
class Application implements ArrayAccess {
    private $extensions;
    /**
     * The encoder of the response.
     */
    private $encoder;
    /**
     * The router to be used to handle
     * each request received.
     * 
     * @var Handle\Router
     */
    private $router;

    public function __construct($encoder=null)
    {
        $this->extensions = array();

        $this->encoder = $encoder;

        $this->router = new Handle\Router;
        $this->router->setFallback(
            '404',
            function(Request $req, Response $res) {
              return $res->end("<h1>404</h1>Not Found <i>{$req->path->absolute}</i>", 404);
            }
        );
    }

    /**
     * It handles non-standard properties
     * that Application instances may
     * provide.
     */
    public function offsetGet($offset)
    {
        return $this->extensions[$offset];
    }

    public function offsetExists($offset){}
    public function offsetSet($offset, $value){}
    public function offsetUnset($offset){}

    public function get(string $routePath, callable $routeHandler) {
        return $this->router->get($routePath, $routeHandler);
    }

    public function post(string $routePath, callable $routeHandler) {
        return $this->router->post($routePath, $routeHandler);
    }

    /**
     * It handles non-standard methods that
     * the Application instance may provide.
     */
    public function __call($methodname, $args) {
        if (!in_array($methodname, ALLOWED_METHODS)) {
            //if (substr($methodname, 0, 3) == "ext") {
            //    $methodname = substr($methodname, 0, 3);
            //    return $this->extensions[$methodname]($args);
            //} else
            throw new Exceptions\ProhibitedMethodException("Prohibited Method: {$methodname}");
        }
    }

    public function use($extensionName, ...$extensionAddons) {
        if (sizeof($extensionAddons) == 0) throw new ArgumentCountError("The 'use' method requires not only a name for a service or extension, but also arguments for it");

        list($extensionArgument, $extensionHandler) = sizeof($extensionAddons) < 2 ? [null, $extensionAddons[0]] : $extensionAddons;

        switch($extensionName) {
            case 'fallback':
                if (!is_string($extensionArgument) || !is_callable($extensionHandler))
                    throw new TypeError("Fallback name must be a string and fallback handler must be a function");
                
                $this->router->setFallback($extensionArgument, $extensionHandler);
                break;
            case 'router':
                if(!is_a($extensionHandler, Handle\Router::class))
                    throw new TypeError("Custom router must be of type {Handle\Router::class}");

                $this->router = $extensionHandler;
                break;
            default:
                //require "extensions/$extension_name/__all__.php";
                //eval("use $extension_name;");
                $this->extensions[$extensionName] = $extensionHandler;
                break;
        }
    }

    /** 
     * Starts to handle the requests
     * using the router stored on 
     * `$this->router`.
    */
    public function run() {
        $this->router->doHandle();
    }

    public function __toString()
    {
        return "<Application instance>";
    }
}
