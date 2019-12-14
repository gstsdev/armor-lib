<?php

use Armor\HandlingTools\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase {
    public function testNormallyCreatingInstance() {
        $req = new Request('get', '/users/1234/1', ['user' => 1234, 'userinfo' => 'profile']);

        $this->assertInstanceOf(Request::class, $req);

        $this->assertClassHasAttribute('path', 'Armor\HandlingTools\Request');
        $this->assertClassHasAttribute('_query', 'Armor\HandlingTools\Request');
        $this->assertClassHasAttribute('method', 'Armor\HandlingTools\Request');
    }

    public function testOnlyAllowsQueryAttributeForGet() {
        $postReq = new Request('post', '/users/insert/1234', ['user' => 1234], ['name' => 'AnyUserName', 'status' => 'Active Account']);
        $this->expectExceptionMessage('Method doesn\'t have query parameters');
        $postReq->query->name == 'admin';
    }

    public function testOnlyAllowsBodyAttributeForPost() {
        $getReq = new Request('get', '/users/12345/10', ['user' => 1234, 'userinfo' => 'filter_topics'], ['topics' => ['programming', 'php']]);
        $this->expectExceptionMessage('Method doesn\'t have a request body');
        is_array($getReq->body->topics);
    }
}