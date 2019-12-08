<?php
require "../src/Application.php";

use Armor\HandlingTools\Request;
use Armor\HandlingTools\Response;

$app = new Armor\Application();

$app->get('/path/to/get', function(Request $req, Response $res) {
    assert(isset($req->path));
    assert(isset($req->query));

    assert(isset($res->append));
    assert(isset($res->end));

    return $res->end();
});

$app->post('/path/to/post', function(Request $req, Response $res) {
    assert(isset($req->path));
    assert(isset($req->body));

    assert(isset($res->append));
    assert(isset($res->end));

    return $res->end();
});


$app->run();