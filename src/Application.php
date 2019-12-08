<?php 
namespace Armor;

require_once "resources/Exceptions/exceptions.php";
require_once "resources/handlingtools.php";

use Armor\Exceptions as Exceptions;
use Armor\HandlingTools as HandlingTools;

use ArgumentCountError;
use Armor\HandlingTools\Request;
use Armor\HandlingTools\Response;
use ArrayAccess;
use TypeError;

const ALLOWED_METHODS = array('get', 'post');

/**
 * Creates an Application instance, which
 * is responsible for setting the routes and
 * handling the requests
 * 
 * @param Callable $encoder
 */
class Application implements ArrayAccess {
    private $handlers, $fallbacks, $extensions;
    private $encoder;
    private $customRouter;

    public function __construct($encoder=null)
    {
        $this->handlers = array(
            'get' => array(), 
            'post' => array()
        );

        $this->fallbacks = array(
            '404' => function(Request $req, Response $res) {
                return $res->end("<h1>404</h1>Not Found <i>{$req->path->absolute}</i>", 404);
            }
        );
        $this->extensions = array();

        $this->encoder = $encoder;

        $this->customRouter = false;
    }

    /**
     * It handles non-standard properties
     * that Application instances can
     * provide
     */
    public function offsetGet($offset)
    {
        return $this->extensions[$offset];
    }

    public function offsetExists($offset){}
    public function offsetSet($offset, $value){}
    public function offsetUnset($offset){}

    /**
     * It handles non-standard methods that
     * the Application instance can provide
     * 
     * @return RouteInterface
     */
    public function __call($methodname, $args) {
        if (!in_array($methodname, ALLOWED_METHODS)) {
            //if (substr($methodname, 0, 3) == "ext") {
            //    $methodname = substr($methodname, 0, 3);
            //    return $this->extensions[$methodname]($args);
            //} else
            throw new Exceptions\ProhibitedMethodException("Prohibited Method: $methodname");
        }

        if (sizeof($args) < 2 || sizeof($args) > 2)
            throw new ArgumentCountError("It should have a route and a handler");

        list($route, $handler) = $args;

        $route = $route[0] != "/" ? "/" . $route : $route;

        list($route, $params, $parsers) = $this->convertRouteToRegex($route);

        if (!is_callable($handler))
            throw new TypeError("Handler must be a function");

        array_push($this->handlers[$methodname], new HandlingTools\Route($route, $params, $handler, $parsers));

        return new HandlingTools\RouteInterface($this->handlers[$methodname][sizeof($this->handlers[$methodname])-1]);
    }

    private function convertRouteToRegex($route) {
        $params = array();
        $parsers = array();

        //$pathto = "/user/12085018232";
        //$matching = "/user/$(userid)/$(userconfig)";
        ///@debug print($route . preg_match("/\\$\((\\w+)(.*?)\)/i", $route) . "<br>");

        $rgx = preg_replace_callback("/\\$\((\\w+)(.*?)\)/i", function($matches) use(&$params, &$parsers) {
            ///@debug print_r(array_slice($matches, 2));
            $variable = $matches[1];
            $params[$variable] = null;
            $parsers[$variable] = $matches[2];
            return "(\\w+)";
        }, $route);

        $rgx = "/^" . str_replace('/', '\/', $rgx) . "$/";

        return array($rgx, $params, $parsers);
    }

    public function use($extension_name, ...$extension_addons) {
        if (sizeof($extension_addons) == 0) throw new ArgumentCountError("The 'use' method requires not only a name for a service or extension, but also arguments for it");

        list($extension_argument, $extension_handler) = sizeof($extension_addons) < 2 ? [null, $extension_addons[0]] : $extension_addons;

        switch($extension_name) {
            case 'fallback':
                if (!is_string($extension_argument) || !is_callable($extension_handler))
                    throw new TypeError("Fallback name must be a string and fallback handler must be a function");
                $this->fallbacks[$extension_argument] = $extension_handler;
                break;
            case 'router':
                $this->customRouter = $extension_handler;
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
     * 
     * Optionally, if a `customRouter` has been defined,
     * all work to create request and response objects,
     * to get the right request handler, and to generate
     * exceptions in case of failures will be assigned to it.
    */
    public function run() {
        $requestCustomParameters = array();
        $requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
        $requestURI = $_SERVER["REQUEST_URI"];
        
        $path = parse_url($requestURI, PHP_URL_PATH);
        parse_str(parse_url($requestURI, PHP_URL_QUERY), $query);

        $requestBody = $requestMethod == "POST" ? $_POST : $query;

        if (!$this->customRouter) {
            $finalResponse = null;

            foreach ($this->handlers[$requestMethod] as $route_handler) {
                if ($route_handler->match($path)) {
                    $finalResponse = $route_handler->getCallback();
                    $requestCustomParameters = $route_handler->getParsedRouteParameters();
                    break;
                }
            }

            if ($finalResponse === null) {
                $finalResponse = $this->fallbacks['404'];
            }

            $finalResponse = is_callable($finalResponse) ? $finalResponse->bindTo($this) : $finalResponse;

            $result = call_user_func($finalResponse, new Request($requestMethod, $path, $requestCustomParameters, $requestBody), new Response($this->encoder));

            if (!$result)
                throw new Exceptions\ResponseCompletionNotCompletedException();

        } else {
            call_user_func($this->customRouter, $requestMethod, $path, $requestBody, $this->encoder);
        }
    }

    public function __toString()
    {
        return "<Application instance>";
    }
}