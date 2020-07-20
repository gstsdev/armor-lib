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
        $app->set('foo1', 'Foo');
        $app->use('foo2', 'Foo');
        $this->assertNotNull($app['foo1']);
        $this->assertNotNull($app['foo2']);
        $this->assertEquals($app['foo1'], 'Foo');
        $this->assertEquals($app['foo2'], 'Foo');

        $app->set('bar1', 123);
        $app->use('bar2', 123);
        $this->assertNotNull($app['bar1']);
        $this->assertNotNull($app['bar2']);
        $this->assertEquals($app['bar1'], 123);
        $this->assertEquals($app['bar2'], 123);

        $app->set('boo1', 3.14);
        $app->use('boo2', 3.14);
        $this->assertNotNull($app['boo1']);
        $this->assertNotNull($app['boo2']);
        $this->assertEquals($app['boo1'], 3.14);
        $this->assertEquals($app['boo2'], 3.14);
    }
}