<?php

use Armor\Handle\RequestPath;
use PHPUnit\Framework\TestCase;

class RequestPathTest extends TestCase {
    public function testNormallyCreatingInstanceAndSettingAttributes() {
        $path = new RequestPath('/user/12345/1', ['userid' => '12345', 'userinfo' => 'profile']);

        $this->assertInstanceOf(RequestPath::class, $path);

        $this->assertClassHasAttribute('absolute', RequestPath::class);
        $this->assertEquals('/user/12345/1', $path->absolute);

        $this->assertClassHasAttribute('placeholders', RequestPath::class);
        $this->assertEquals('12345', $path['userid']);
        $this->assertEquals('profile', $path['userinfo']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Path parameters are read-only");
        $path['userid'] = null;
    }
}