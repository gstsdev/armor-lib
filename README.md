# armor-lib

[![Packagist Version](https://img.shields.io/packagist/v/armor/framework)](https://packagist.org/packages/armor/framework)
[![Build Status](https://travis-ci.org/14mPr0gr4mm3r/armor-lib.svg?branch=master)](https://travis-ci.org/14mPr0gr4mm3r/armor-lib)
[![Armor Health](https://github.com/14mPr0gr4mm3r/armor-lib/workflows/Armor%20Health/badge.svg?branch=master)](https://github.com/14mPr0gr4mm3r/armor-lib/actions)
[![Minimum PHP Version Support](https://img.shields.io/packagist/php-v/armor/framework)](https://php.net)
[![Packagist Downloads](https://img.shields.io/packagist/dt/armor/framework)](https://packagist.org/packages/armor/framework/stats)
[![License](https://img.shields.io/packagist/l/armor/framework)](https://github.com/14mPr0gr4mm3r/armor-lib/blob/master/LICENSE)

Armor (**A** **R**outing and **MOR**e Things Framework) aims to be an useful routing framework for PHP developers.

It implements classes and methods to handle requests and respond them. And besides this, it can even receive **extensions**, or the famous "**plugins**", whatever you like to call it.

Below, you can get started on how to install it, how to use it to handle requests and, for now, how to create templates
and send them as response, by using the extension library, [_ArmorUI_](https://github.com/14mPr0gr4mm3r/armor-ui).


## Getting Started

### Installing Armor

This step is very simple: just use [_Composer_](https://getcomposer.org)!

```
composer require armor/framework
```

### Implemeting Armor

First of all, when creating an application that uses Armor, you have to create an application instance:

```php
<?php
require "../vendor/autoload.php";

$app = new Armor\Application();
```

**Note**: Optionally, you can pass a text encoder as an argument, which will be used up front to encode the response content. By default, it uses the `utf8_encode`.

And at the bottom of the main file, put the call to the `Application#run` method:

```php
$app->run();
```

So, the file should be something like that:

```php
<?php
require "../vendor/autoload.php";

$app = new Armor\Application();

//The request handlers
/**
 * $app->get('/', function() {...});
 * 
 * $app->get('/path/to', function() {...});
 * 
 */

$app->run();
```

**Note: you should create the file `.htaccess` to redirect all requests to the main (`index.php`) file**

Now, when talking about handling the requests properly, we must say that Armor offers a simple and easy way to handle a request, based on the method that has been used to perform it:

```php
// Handles a GET request for "/path/to"
$app->get('/path/to', function() {
    //...
});
// Handles a POST request for "/path/to"
$app->post('/path/to', function() {
    //...
});
```

At the moment, Armor only handles GET and POST requests. But, in the future, it may support more.

The callback that is passed as argument must receive **at least** two parameters: a `Request` object and 
a `Response` object.

The `Request` object provides information about the path requested, the search-query parameters, and the **route parameters**. The last name may not sound familiar, but if you are a back-end developer, you might have seen something like this:

```php
$app->get('/path/to/$(section)', function(Request $req, Response $res) {
    if ($req->path['section'] == 'something') {
        //do something
    }
    //...
});
```

And the `Response` object provides a lot of methods for appending content to the response.

Below, you can see a small example of handling a request and sending a response:

```php
$app = new Armor\Application();

$app->get('/', function(Request $req, Response $res) {
    $res->append("<html>
        <body>
            <h1>Hello, World!</h1>
            <p>This is an example page, and you are accessing {$req->path->absolute}</p>
        </body>
    </html>");

    return $res->end();
});

$app->run();
```

As you can see, we are handling a request to the path `/`. We append a simple message to the response, which uses the `absolute` value of the `path` property of the `Request` object. And, finally, we complete the response, and returns it final result. That "return" is used by Armor to know if the response has been correctly built, or something unexpected has occurred. If it does, Armor throws a `ResponseNotCorrectlyCompletedException`.

Well, there is a lot to know about Armor. In the future, it may be fully covered by a more detailed documentation.

### A little talk about using templates

There is an extension library that can be used to create some UI, called _ArmorUI_. For now, it provides two classes: `TemplateManager`, which is responsible for loading templates from their directories, and `Template`, which is the template object itself. It works like that:

```php
/** 
 * This example is a snippet taken and adapted from the 'example01', available at https://github.com/14mPr0gr4mm3r/armor-examples
 * @author 14mPr0gr4mm3r
 * @license MIT
*/

$templ = new ArmorUI\TemplateManager("./", ['header', 'index']);

$app->get('/', function(Request $req, Response $res) use($templ) {
    $templ->getTemplate('index')->sendAsResponse($res);

    return $res->end();
});
```

Here, we loaded two templates: "_header.templ.armor_" and "_index.templ.armor_". They are on the same directory that the 
source file is. We load the manager from the inside of the closure, and use it to load the `index` template (`getTemplate`) and to 
send it. For sending the template, we pass the `Response` object by reference to the `Template#sendAsResponse` method. And then, we finish the request handling process.

Instead, to avoid adding the `use` keyword to "request handling closures", we could use the `Application#use` method, 
like this:

```php
$templ = new ArmorUI\TemplateManager("./", ['header', 'index']);

$app->use('templ', $templ);

$app->get('/', function(Request $req, Response $res, Application $app) {
    $app['templ']->getTemplate('index')->sendAsResponse($res);

    return $res->end();
});
```

And here, we see the third, but **optional**, parameter: the `Application` instance created by the user and used to 
define the route.

## Final Considerations

I admit that those were a very few lines. But, in the future, the quality and detail of the documentation might get better.

Good studies, from 14mPr0gr4mm3r.