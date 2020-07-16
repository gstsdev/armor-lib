<?php

namespace Armor\Handle;

// require_once __DIR__."/../../vendor/autoload.php";

use \Armor\Handle\Request;
use \Armor\Handle\Response;
use \Armor\Handle\Route;
use \Armor\Handle\RouteInterface;
use \Armor\Exceptions\ResponseNotCorrectlyCompletedException;

use TypeError;

/**
 * This class is used to create the default and custom routers
 * of the application instance.
 * 
 */
class Router {
  /**
   * The encoder of the response. It's passed to the `Response` object.
   * 
   * @var \callable|null
   */
  private $encoder;
  /**
   * The array of routes predefined by the class, and populated with
   * the definitions of the framework user.
   * 
   * @var \array
   */
  private $routes;
  /**
   * The set of possible fallbacks that may be defined by the framework user,
   * or even by the framework classes itselves, like the `Application` class (which
   * defines the `404` fallback).
   * 
   * @var \array
   */
  private $fallbacks;

  /**
   * @param \callable|null $encoder The encoder of the response. It's passed to the `Response` object.
   */
  public function __construct($encoder=null) {

    $this->routes = array(
      'get' => array(), 
      'post' => array()
    );

    $this->fallbacks = array();

    $this->encoder = $encoder;
  }

  /**
   * Define a route which request method must be `GET`.
   * 
   * A shorthand to `$this->registerRoute("get", $routePath, $routeHandler)`.
   * 
   * @param \string $routePath
   * @param \callable $routeHandler
   * @return RouteInterface
   */
  public function get(string $routePath, callable $routeHandler) {
    return $this->registerRoute("get", $routePath, $routeHandler);
  }

  /**
   * Define a route which request method must be `POST`.
   * 
   * A shorthand to `$this->registerRoute("post", $routePath, $routeHandler)`.
   * 
   * @param \string $routePath
   * @param \callable $routeHandler
   * @return RouteInterface
   */
  public function post(string $routePath, callable $routeHandler) {
    return $this->registerRoute("post", $routePath, $routeHandler);
  }
  
  /**
   * Registers an application route, considering the method that
   * must be used by the request, the path of the route and accepts
   * a route handler.
   * 
   * @return RouteInterface
   */
  public function registerRoute(string $method, string $routePath, callable $routeHandler) {
    $routePath = $routePath[0] != "/" ? "/" . $routePath : $routePath;

    list($routePath, $params, $parsers) = $this->convertRoutePathToRegex($routePath);

    if (!is_callable($routeHandler))
      throw new TypeError("Handler must be a function");

    array_push($this->routes[$method], new Route($routePath, $params, $routeHandler, $parsers));

    return new RouteInterface($this->routes[$method][sizeof($this->routes[$method])-1]);
  }

  /**
   * Perform the appropriate parsing to obtain a regex from the path string.
   * 
   * @param \string $routePath The path string to be converted to a "path regex string".
   * @return \array
   */
  private function convertRoutePathToRegex($routePath) {
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
    }, $routePath);

    $rgx = "/^" . str_replace('/', '\/', $rgx) . "$/";

    return array($rgx, $params, $parsers);
  }

  /**
   * Perform the handling of a request
   * received by the application, 
   * sending the response accordingly
   * to the route handlers setted.
   * 
   * If the requested route/path is
   * not found or doesn't exists, 
   * it sends a 404 page.
   * 
   * @param \Armor\Application &$parentApplication The application instance defined by the user and which is 
   * calling this method.
   */
  public function doHandle(\Armor\Application &$parentApplication) {
    $finalResponse = null;

    list($requestObject, $responseObject) = [$this->buildRequestObject(), $this->buildResponseObject()];

    foreach ($this->routes[$requestObject->method] as $route) {
      if ($route->match($requestObject->path->absolute)) {
        $finalResponse = $route->getCallback();
        $requestObject->injectCustomParametersFromRoute($route);
        // $requestCustomParameters = $route->getParsedRouteParameters();
        break;
      }
    }

    if ($finalResponse === null) {
      $finalResponse = $this->fallbacks['404'];

      if(!is_callable($finalResponse))
        throw new TypeError("Handling function expected, '{gettype($finalResponse)}' got");
    }

    $result = call_user_func(
      $finalResponse,
      $requestObject,
      $responseObject,
      $parentApplication
    );

    if (!$result)
      throw new Exceptions\ResponseNotCorrectlyCompletedException();
  }

  /**
   * Build the `Request` object that will be passed to the callback
   * defined by the user to the route.
   * 
   * @return Request
   */
  private function buildRequestObject() {
    $requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
    $requestURI = $_SERVER["REQUEST_URI"];

    $path = parse_url($requestURI, PHP_URL_PATH);
    parse_str(parse_url($requestURI, PHP_URL_QUERY), $query);

    $requestBody = $requestMethod == "POST" ? $_POST : $query;

    return new Request($requestMethod, $path, [], $requestBody);
  }

  /**
   * Build the `Response` object that will be passed to the callback
   * defined by the user to the route.
   * 
   * @return Response
   */
  private function buildResponseObject() {
    return new Response($this->encoder);
  }

  /**
   * Sets a fallback to the router.
   * 
   * It's used by the `Application` class to set the default fallback
   * to the _404 - Not Found_ page.
   * 
   * @param \string $fallbackName
   * @param \callable $fallbackHandler
   */
  public function setFallback(string $fallbackName, callable $fallbackHandler) {
    $this->fallbacks[$fallbackName] = $fallbackHandler;
  }
}