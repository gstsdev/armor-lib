<?php

use Armor\Handle\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase {
  public function testNormallyCreatingInstance() {
    $res = new Response(function($content) { return \utf8_encode($content); });

    $this->assertInstanceOf(Response::class, $res);

    return $res;
  }

  /**
   * @depends testNormallyCreatingInstance
   */
  public function testAppendsContentToResponseAsStringAndAsFunctionNormally(Response $res) {
    $this->assertTrue($res->append("Hello, World!"));

    $this->assertTrue($res->append(function() { return "Hello again, World!"; }));

    return $res;
  }

  /**
   * @depends testAppendsContentToResponseAsStringAndAsFunctionNormally
   */
  public function testContentIsBeingReallyAppended(Response $res) {
    $this->expectOutputString("Hello, World!\nHello again, World!\n");

    $result = $res->end();

    $this->assertTrue($result);
  }

  /**
   * @depends testNormallyCreatingInstance
   */
  public function testCanReceiveExternalAdditionalFieldsAndFunctions(Response $res) {
    $res->foo = "Foo";
    $this->assertNotNull($res->foo);
    $this->assertEquals($res->foo, "Foo");

    $res->bar = function() {
      return "Hello, World!";
    };
    $this->assertNotNull($res->bar);
    $this->assertTrue(is_callable($res->bar));
    $this->assertEquals($res->bar(), "Hello, World!");

    return $res;
  }

  /**
   * @depends testCanReceiveExternalAdditionalFieldsAndFunctions
   */
  public function testReturnsNullToNonExistentFieldsAndThrowsErrorToNonCallableField(Response $res) {
    $this->assertNull($res->boo);
    $this->assertNull($res->faa);

    $undefinedMethod1 = "amazingAction";
    $this->expectExceptionMessage("'$undefinedMethod1' is not a method, or does not exist");
    $res->{$undefinedMethod1}();

    $undefinedMethod2 = "veryAwesomeProcedure";
    $this->expectExceptionMessage("'$undefinedMethod2' is not a method, or does not exist");
    $res->{$undefinedMethod2}();
  }
}