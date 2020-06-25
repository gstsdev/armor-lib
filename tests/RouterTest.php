<?php

use \Armor\Handle\Router;
use \Armor\Handle\RouteInterface;
use PHPUnit\Framework\TestCase;

$GLOBALS['router'] = null;

class RouterTest extends TestCase {
  public function testNormallyCreatingInstance() {
    $GLOBALS['router'] = new Router();

    $this->assertInstanceOf(Router::class, $GLOBALS['router']);

    $this->assertClassHasAttribute('handlers', Router::class);
    $this->assertClassHasAttribute('fallbacks', Router::class);
  }

  public function testAddsRequestHandlersViaConventionalMethod() {
    $this->assertInstanceOf(RouteInterface::class, $GLOBALS['router']->get('/', function($req, $res) { return true; }));
    $this->assertInstanceOf(RouteInterface::class, $GLOBALS['router']->post('/', function($req, $res) { return true; }));
  }

  public function testAddsRequestHandlersViaGlobalMethod() {
    $this->assertInstanceOf(RouteInterface::class, $GLOBALS['router']->registerRoute('get', '/', function($req, $res) { return true; }));
    $this->assertInstanceOf(RouteInterface::class, $GLOBALS['router']->registerRoute('post', '/', function($req, $res) { return true; }));
  }
}