<?php

namespace Armor\Handle;

spl_autoload_register(function($class) {
  try {
      require_once "./" . str_replace('\\', DIRECTORY_SEPARATOR, $class);
  } catch(\Throwable $e) {
      require "../../vendor/autoload.php";
  }
});

use \Armor\Handle\Request;
use \Armor\Handle\Response;
use \Armor\Handle\Route;
use \Armor\Handle\RouteInterface;

use TypeError;

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

  public function setFallback(string $fallback_name, callable $fallback_handler) {
    $this->fallbacks[$fallback_name] = $fallback_handler;
  }

  /**
   * Perform the handling of a request
   * received by the application.
   * 
   * @return int|bool
   */
  public function doHandle(Request $request_object, Response $response_object) {
    $finalResponse = null;

    foreach ($this->routes[$request_object->method] as $route_handler) {
      if ($route_handler->match($path)) {
        $finalResponse = $route_handler->getCallback();
        $requestCustomParameters = $route_handler->getParsedRouteParameters();
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
      new Request($requestMethod, $path, $requestCustomParameters, $requestBody),
      new Response($this->encoder)
    );

    return $result;
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

  /**
   * Registers an application route, considering the method that
   * must be used by the request, the path of the route and accepts
   * a route handler.
   * 
   * @return RouteInterface
   */
  public function registerRoute(string $method, string $route_path, callable $route_handler) {
    $route_path = $route_path[0] != "/" ? "/" . $route_path : $route_path;

    list($route_path, $params, $parsers) = $this->convertRouteToRegex($route_path);

    if (!is_callable($route_handler))
      throw new TypeError("Handler must be a function");

    array_push($this->routes[$method], new Route($route_path, $params, $route_handler, $parsers));

    return new RouteInterface($this->routes[$method][sizeof($this->routes[$method])-1]);
  }

  public function get(string $route_path, callable $route_handler) {
    return $this->registerRoute("get", $route_path, $route_handler);
  }

  public function post(string $route_path, callable $route_handler) {
    return $this->registerRoute("post", $route_path, $route_handler);
  }
}