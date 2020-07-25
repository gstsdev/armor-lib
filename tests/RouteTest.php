<?php

use Armor\Handle\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase {
    public function testNormallyCreatingInstance() {
        $route = new Route('/path/$(section)/to', function($req, $res) { return true; });

        $this->assertInstanceOf(Route::class, $route);

        /*
         * $pattern, $callback;
         * $parameters, $parsers;
         * $custom_parser;
         */

        // PHPUnit 9 doesn't support this anymore
        // $this->assertClassHasAttribute('pattern', Route::class);
        // $this->assertClassHasAttribute('callback', Route::class);
        // $this->assertClassHasAttribute('parameters', Route::class);
        // $this->assertClassHasAttribute('parsers', Route::class);
        // $this->assertClassHasAttribute('custom_parser', Route::class);

        return $route;
    }

    /**
     * @depends testNormallyCreatingInstance
     */
    public function testMatchesSpecificPaths(Route $route) {
        $this->assertTrue((bool)$route->match('/path/123/to'));
        $this->assertTrue((bool)$route->match('/path/123-456/to'));
        $this->assertTrue((bool)$route->match('/path/1234%205%2C6789/to'));
        $this->assertFalse((bool)$route->match('/path/to'));
        $this->assertFalse((bool)$route->match('/def/1234/to'));
        $this->assertFalse((bool)$route->match('/123/456/789'));
    }

    public function testCanParseRouteParameters() {
        $route1 = new Route('/path/$(section)/to',
                            function($req, $res) { return true; });
        $this->assertTrue((bool)$route1->match('/path/123/to'));

        $params1 = $route1->getParsedRouteParameters();

        $this->assertArrayHasKey('section', $params1);


        $route2 = new Route('/path/$(section)/$(post)',
                            function($req, $res) { return true; });
        $this->assertTrue((bool)$route2->match('/path/123456789/101112'));

        $params2 = $route2->getParsedRouteParameters();

        $this->assertArrayHasKey('section', $params2);

        $this->assertArrayHasKey('post', $params2);


        $route3 = new Route('/$(year)/$(section)/to',
                            function($req, $res) { return true; });
        $this->assertTrue((bool)$route3->match('/2019/123456/to'));

        $params3 = $route3->getParsedRouteParameters();
        
        $this->assertArrayHasKey('year', $params3);
        
        $this->assertArrayHasKey('section', $params3);
    }

    public function testCanConvertTheRouteParametersValues() {
        $route4 = new Route('/$(year:toint)/$(section)/to',
                            function($req, $res) { return true; });
        $this->assertTrue((bool)$route4->match('/2019/123456/to'));

        $params4 = $route4->getParsedRouteParameters();

        $this->assertArrayHasKey('year', $params4);
        $this->assertIsInt($params4['year']);
        $this->assertEquals($params4['year'], 2019);

        $this->assertArrayHasKey('section', $params4);


        $route5 = new Route('/$(year:toint)/$(section:toupper)/to',
                            function($req, $res) { return true; });
        $this->assertTrue((bool)$route5->match('/2019/posts/to'));

        $params5 = $route5->getParsedRouteParameters();

        $this->assertArrayHasKey('year', $params5);
        $this->assertIsInt($params5['year']);
        $this->assertEquals($params5['year'], 2019);

        $this->assertArrayHasKey('section', $params5);
        $this->assertEquals('POSTS', $params5['section']);


        $route6 = new Route('/$(user)/$(profile:toint:tobool)',
                            function($req, $res) { return true; });
        $this->assertTrue((bool)$route6->match('/user12308121/1'));
        
        $params6 = $route6->getParsedRouteParameters();

        $this->assertArrayHasKey('user', $params6);
        $this->assertArrayHasKey('profile', $params6);

        $this->assertIsBool($params6['profile']);
        $this->assertTrue($params6['profile']);
    }
}