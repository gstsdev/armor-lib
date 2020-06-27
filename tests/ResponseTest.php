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
}