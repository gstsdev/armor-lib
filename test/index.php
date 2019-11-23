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