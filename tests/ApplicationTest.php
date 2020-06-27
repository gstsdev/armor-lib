<?php

// use Armor\Exceptions\ProhibitedMethodException;

use Armor\Application;
use Armor\Handle\Route;
use Armor\Handle\RouteInterface;
use PHPUnit\Framework\TestCase;

$GLOBALS['app'] = null;

class ApplicationTest extends TestCase {
    public function testNormallyCreatingInstance() {
        $GLOBALS['app'] = new Application();

        $this->assertInstanceOf(Application::class, $GLOBALS['app']);

        // PHPUnit 9 doesn't support this anymore
        // $this->assertClassHasAttribute('extensions', Application::class);
        // $this->assertClassHasAttribute('encoder', Application::class);
        // $this->assertClassHasAttribute('router', Application::class);
    }

    public function testAddsRequestHandlers() {
        $this->assertInstanceOf(RouteInterface::class, $GLOBALS['app']->get('/', function($req, $res) { return true; }));
        $this->assertInstanceOf(RouteInterface::class, $GLOBALS['app']->post('/', function($req, $res) { return true; }));
    }

    public function testDoesNotAllowOtherMethodsThanGetAndPost() {
        /// @todo Implement the use of the exception class itself
        /// with the method `TestCase#expectException`

        // $this->expectException(ProhibitedMethodException::class);
        $this->expectExceptionMessage('Prohibited Method: put');
        $GLOBALS['app']->put('/', function($req, $res) { return true; });

        // $this->expectException(ProhibitedMethodException::class);
        $this->expectExceptionMessage('Prohibited Method: delete');
        $GLOBALS['app']->delete('/', function($req, $res) { return true; });
    }
}