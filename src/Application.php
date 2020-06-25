<?php

namespace Armor;

spl_autoload_register(function($class) {
    require_once "./" . \strreplace('\\', DIRECTORY_SEPARATOR, $class);
});

require __DIR__."/../vendor/autoload.php";
//require_once "resources/Exceptions/exceptions.php";
//require_once "resources/handlingtools.php";

use Armor\Exceptions as Exceptions;
use Armor\Handle as Handle;

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

    public function get(string $route_path, callable $route_handler) {
        return $this->router->get($route_path, $route_handler);
    }

    public function post(string $route_path, callable $route_handler) {
        return $this->router->post($route_path, $route_handler);
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

    public function use($extension_name, ...$extension_addons) {
        if (sizeof($extension_addons) == 0) throw new ArgumentCountError("The 'use' method requires not only a name for a service or extension, but also arguments for it");

        list($extension_argument, $extension_handler) = sizeof($extension_addons) < 2 ? [null, $extension_addons[0]] : $extension_addons;

        switch($extension_name) {
            case 'fallback':
                if (!is_string($extension_argument) || !is_callable($extension_handler))
                    throw new TypeError("Fallback name must be a string and fallback handler must be a function");
                
                $this->router->setFallback($extension_argument, $extension_handler);
                break;
            case 'router':
                if(!is_a($extension_handler, Handle\Router::class))
                    throw new TypeError("Custom router must be of type {Handle\Router::class}");

                $this->router = $extension_handler;
                break;
            default:
                //require "extensions/$extension_name/__all__.php";
                //eval("use $extension_name;");
                $this->extensions[$extension_name] = $extension_handler;
                break;
        }
    }

    /** 
     * Starts to handle the requests,
     * sending the response according
     * to the handlers setted. 
     * If the request route/path is
     * not found, it sends a 404 page
    */
    public function run() {
        $requestCustomParameters = array();
        $requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
        $requestURI = $_SERVER["REQUEST_URI"];
        
        $path = parse_url($requestURI, PHP_URL_PATH);
        parse_str(parse_url($requestURI, PHP_URL_QUERY), $query);

        $requestBody = $requestMethod == "POST" ? $_POST : $query;

        $result = $this->router->doHandle(
            new Request($requestMethod, $path, $requestCustomParameters, $requestBody),
            new Response($this->encoder)
        );

        if (!$result)
            throw new Exceptions\ResponseNotCorrectlyCompletedException();
    }

    public function __toString()
    {
        return "<Application instance>";
    }
}
