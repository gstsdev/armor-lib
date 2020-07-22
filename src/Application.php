<?php

/**
 * Provides the main class of the Armor framework, the class `Application`.
 * 
 * @author Gustavo Sampaio (14mPr0gr4mm3r)
 */

namespace Armor;

use \Armor\Exceptions\ProhibitedRouteRequestMethodException;
use \Armor\Handle as Handle;

use ArgumentCountError;
use ArrayAccess;
use TypeError;

/**
 * Creates an Application instance, which
 * is responsible for setting the routes and
 * handling the requests.
 * 
 */
class Application implements ArrayAccess {
    /**
     * The extensions that the user may have defined.
     * 
     * @var \array
     */
    private $extensions;
    /**
     * The encoder of the response.
     * 
     * @var \callable
     */
    private $encoder;
    /**
     * The router to be used to handle
     * each request received.
     * 
     * @var Handle\Router
     */
    private $router;

    const ALLOWED_METHODS = array('get', 'post');

    /**
     * @param \callable $encoder
     */
    public function __construct($encoder=null)
    {
        $this->extensions = array();

        $this->encoder = $encoder;

        $this->router = new Handle\Router($encoder);
        $this->router->setFallback(
            '404',
            function(Handle\Request $req, Handle\Response $res) {
              return $res->end("<h1>404</h1>Not Found <i>{$req->path->absolute}</i>", 404);
            }
        );
    }

    /**
     * @ignore
     * 
     * It handles non-standard properties
     * that Application instances may
     * provide.
     */
    public function offsetGet($offset)
    {
        return $this->extensions[$offset];
    }

    /** @ignore */
    public function offsetExists($offset){}
    /** @ignore */
    public function offsetSet($offset, $value){}
    /** @ignore */
    public function offsetUnset($offset){}

    /**
     * Define a route which request method must be `GET`.
     * 
     * @param \string $routePath The path that the user expect to be requested via method `GET`.
     * Here is where the user may also define the route parameters, by using the following notation:
     * `/[.../] $(<route_parameter_name>[:tolower|toupper|toint|tobool|toparse]*) [/]`
     * @param \callable $routeHandler The callback that will handle the request.
     * @return Handle\RouteInterface
     */
    public function get(string $routePath, callable $routeHandler) {
        return $this->router->get($routePath, $routeHandler);
    }

    /**
     * Define a route which request method must be `POST`.
     * 
     * @param \string $routePath The path that the user expect to be requested via method `POST`.
     * Here is where the user may also define the route parameters, by using the following notation:
     * `/[.../] $(<route_parameter_name>[:tolower|toupper|toint|tobool|toparse]*) [/]`
     * @param \callable $routeHandler The callback that will handle the request.
     * @return Handle\RouteInterface
     */
    public function post(string $routePath, callable $routeHandler) {
        return $this->router->post($routePath, $routeHandler);
    }

    /**
     * @ignore
     * 
     * It handles non-standard methods that
     * the Application instance may provide. 
     */
    public function __call($methodname, $args) {
        if (!in_array($methodname, Application::ALLOWED_METHODS)) {
            //if (substr($methodname, 0, 3) == "ext") {
            //    $methodname = substr($methodname, 0, 3);
            //    return $this->extensions[$methodname]($args);
            //} else
            throw new ProhibitedRouteRequestMethodException("Prohibited Route Request Method: {$methodname}");
        }
    }

    /**
     * Define an extension that the application will use and
     * that the developer may get via the "subscription syntax"
     * allowed by this class, or defines a fallback on the router,
     * or even defines a custom router.
     * 
     * @param \string $extensionName
     * @param \array $extensionAddons
     */
    public function set($extensionName, ...$extensionAddons) {
        if (sizeof($extensionAddons) == 0)
            throw new ArgumentCountError("This method requires not only a name for a service " . 
                                         "or extension, but also arguments for it");

        list($extensionArgument, $extensionHandler) = sizeof($extensionAddons) < 2
                                                      ? [null, $extensionAddons[0]]
                                                      : $extensionAddons;

        switch($extensionName) {
            case 'fallback':
                if (!is_string($extensionArgument) || !is_callable($extensionHandler))
                    throw new TypeError("Fallback name must be a string and fallback handler must be a function");
                
                $this->router->setFallback($extensionArgument, $extensionHandler);
                break;
            case 'router':
                if(!is_a($extensionHandler, Handle\Router::class))
                    throw new TypeError("Custom router must be of type " . Handle\Router::class);

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
     * @ignore
     * 
     * Define an extension that the application will use and
     * that the developer may get via the "subscription syntax"
     * allowed by this class, or defines a fallback on the router,
     * or even defines a custom router.
     * 
     * This is deprecated. Now it's just an alias to `Application#set()`,
     * which you should use instead from now.
     * 
     * @deprecated
     * @param \string $extensionName
     * @param \array $extensionAddons
     */
    public function use($extensionName, ...$extensionAddons) {
        $this->set($extensionName, ...$extensionAddons);
    }

    /** 
     * Starts to handle the requests
     * using the router stored on 
     * `$this->router`.
    */
    public function run() {
        $this->router->doHandle($this);
    }

    /** @ignore */
    public function __toString()
    {
        return "<Application instance>";
    }
}
