<?php

require "../src/Application.php";
require "../src/extensions/ArmorTemplating/__all__.php";

$app = new Armor\Application();

$templ = new \Armor\Extensions\ArmorTemplating\TemplateManager("./", []);

$app->use('templ', $templ);

$app->run();