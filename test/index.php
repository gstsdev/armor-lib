<?php
/**
 * A simple example of use of Armor
 * @author 14mPr0gr4mm3r
 * @license GPL-3.0
 */

require_once "../src/Application.php";
require_once "../src/extensions/ArmorTemplating/__all__.php";

use Armor\HandlingTools\Request;
use Armor\HandlingTools\Response;

$app = new Armor\Application();
$templ = new ArmorTemplating\TemplateManager("./", ["header", "index"]);

class User {
    private $id, $name, $desc, $birthday;

    public function __construct(int $id, string $name, $desc, $birthday)
    {
        $this->id = $id;
        $this->name = $name;
        $this->desc = $desc;
        $this->birthday = $birthday;
    }

    public static function loadFromID(int $id) {
        $db = array(123456 => array('name' => 'Fulano', 'desc' => null, 'birthday' => '12/10/1979'));
        return array_key_exists($id, $db) ? new User($id, ...array_values($db[$id])) : exit('User not found');
    }

    public function __toString()
    {
        return "User({ id: {$this->id}, name: {$this->name}, desc: {$this->desc}, birthday: {$this->birthday} })";
    }
}

$my_handlers = array(
    function(Request $req, Response $res) {
        $template = Armor\HandlingTools\Response::loadContentFrom("pages.json", Response::JSON_PARSE);
        $keys = explode('/', substr($req->path, 1));
    
        $content = $template;
    
        foreach ($keys as $key) {
            $content = $content[$key];
        }
    
        return $res->end($content['content']);
    },
    function(Request $req, Response $res) use($templ) {
        $templ->getTemplate("index")->sendAsResponse($res);
    
        return $res->end();
    } 
);

$app->use('MyHandlers', $my_handlers);

$app->get('/users/$(user:toint:toparse)', function(Request $req, Response $res) {
    $res->append((string)$req->path['user']);
    return $res->end();
})->setParser(function($id) { return User::loadFromID($id); });

$app->get('/examples/$(examplename)', function(Request $req, Response $res) {
    switch($req->path['examplename']) {
        case 'templates_json':
            return call_user_func($this['MyHandlers'][0], $req, $res);
        case 'templates_framework':
            return call_user_func($this['MyHandlers'][1], $req, $res);
        default:
            break;
    }

    return $res->end("", 404);
});

$app->run();