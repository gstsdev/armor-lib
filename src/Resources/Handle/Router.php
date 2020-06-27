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
 */
class Router {
  private $routes;
  private $fallbacks;

  public function __construct() {

    $this->routes = array(
      'get' => array(), 
      'post' => array()
    );

    $this->fallbacks = array();

  }

  public function get(string $routePath, callable $routeHandler) {
    return $this->registerRoute("get", $routePath, $routeHandler);
  }

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
   * @return int|bool
   */
  public function doHandle() {
    $finalResponse = null;

    list($requestObject, $responseObject) = [$this->buildRequestObject(), $this->buildResponseObject()];

    foreach ($this->routes[$requestObject->method] as $route) {
      if ($route->match($path)) {
        $finalResponse = $route->getCallback();
        $requestObject->injectCustomParameters($route);
        // $requestCustomParameters = $route->getParsedRouteParameters();
        break;
      }
    }

    if ($finalResponse === null) {
      $finalResponse = $this->fallbacks['404'];

      if(!is_callable($finalResponse))
        throw new TypeError("Handling function expected, '{gettype($finalResponse)}' got");
    }

    $finalResponse->bindTo($this);

    $result = call_user_func(
      $finalResponse,
      $requestObject,
      $responseObject
    );

    if (!$result)
      throw new Exceptions\ResponseNotCorrectlyCompletedException();
  }

  private function buildRequestObject() {
    $requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
    $requestURI = $_SERVER["REQUEST_URI"];

    $path = parse_url($requestURI, PHP_URL_PATH);
    parse_str(parse_url($requestURI, PHP_URL_QUERY), $query);

    $requestBody = $requestMethod == "POST" ? $_POST : $query;

    return new Request($requestMethod, $path, [], $requestBody);
  }

  private function buildResponseObject() {
    return new Response($this->encoder);
  }

  public function setFallback(string $fallbackName, callable $fallbackHandler) {
    $this->fallbacks[$fallbackName] = $fallbackHandler;
  }
}