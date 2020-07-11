<?php

use Armor\Application;
use Armor\Exceptions\ProhibitedRouteRequestMethodException;
use Armor\Handle\Route;
use Armor\Handle\RouteInterface;
use PHPUnit\Framework\TestCase;


class ApplicationTest extends TestCase {
    public function testNormallyCreatingInstance() {
        $app = new Application();

        $this->assertInstanceOf(Application::class, $app);

        // PHPUnit 9 doesn't support this anymore
        // $this->assertClassHasAttribute('extensions', Application::class);
        // $this->assertClassHasAttribute('encoder', Application::class);
        // $this->assertClassHasAttribute('router', Application::class);

        return $app;
    }

    /**
     * @depends testNormallyCreatingInstance
     */
    public function testAddsRequestHandlers(Application $app) {
        $this->assertInstanceOf(RouteInterface::class, $app->get('/', function($req, $res) { return true; }));
        $this->assertInstanceOf(RouteInterface::class, $app->post('/', function($req, $res) { return true; }));
        return $app;
    }

    /**
     * @depends testAddsRequestHandlers
     */
    public function testDoesNotAllowOtherMethodsThanGetAndPost(Application $app) {
        /// @todo Implement the use of the exception class itself
        /// with the method `TestCase#expectException`

        $this->expectException(ProhibitedRouteRequestMethodException::class);
        $this->expectExceptionMessage('Prohibited Route Request Method: put');
        $app->put('/', function($req, $res) { return true; });

        $this->expectException(ProhibitedRouteRequestMethodException::class);
        $this->expectExceptionMessage('Prohibited Route Request Method: delete');
        $app->delete('/', function($req, $res) { return true; });
    }

    /**
     * @depends testNormallyCreatingInstance
     */
    public function testDoesStoreInternalUsableVariablesOnInstance(Application $app) {
        $app->use('foo', 'Foo');
        $this->assertNotNull($app['foo']);
        $this->assertEquals($app['foo'], 'Foo');

        $app->use('bar', 123);
        $this->assertNotNull($app['bar']);
        $this->assertEquals($app['bar'], 123);

        $app->use('boo', 3.14);
        $this->assertNotNull($app['boo']);
        $this->assertEquals($app['boo'], 3.14);
    }
}