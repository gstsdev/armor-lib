<?php

use Armor\Handle\Route;
use Armor\Handle\RouteInterface;
use Armor\Handle\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {
  public function testNormallyCreatingInstance() {
    $router = new Router(null);

    $this->assertInstanceOf(Router::class, $router);

    // PHPUnit 9 doesn't support this anymore
    // $this->assertClassHasAttribute('routes', Router::class);
    // $this->assertClassHasAttribute('fallbacks', Router::class);

    return $router;
  }

  /**
   * @depends testNormallyCreatingInstance
   */
  public function testAddsRequestHandlersViaConventionalMethod(Router $router) {
    $this->assertInstanceOf(RouteInterface::class, $router->get('/', function($req, $res) { return true; }));
    $this->assertInstanceOf(RouteInterface::class, $router->post('/', function($req, $res) { return true; }));
  }

  /**
   * @depends testNormallyCreatingInstance
   */
  public function testAddsRequestHandlersViaGlobalMethod(Router $router) {
    $this->assertInstanceOf(RouteInterface::class, $router->registerRoute('get', '/', function($req, $res) { return true; }));
    $this->assertInstanceOf(RouteInterface::class, $router->registerRoute('post', '/', function($req, $res) { return true; }));
  }
}