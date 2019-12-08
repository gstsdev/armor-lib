# armor-lib

Armor (**A** **R**outing and **MOR**e Things Framework) aims to be an useful routing (and more things) framework for back-end developers.

It implements classes and methods to handle requests and respond them. At first, it seems to be very "contentless". However, it can be quite powerful, as it can receive **extensions**, or the famous "**plugins**", whatever you like to call it.

Below, you can get started on installing it, using it to handle requests and, for now, creating templates and sending them as response, by using the "native extension", _ArmorTemplating_ (well, this name may change along the time).


## Getting Started

### Installing Armor

You can get Armor using _[Composer](https://getcomposer.org)_ just like this:

```composer require 14mpr0gr4mm3r/armor-lib```

And YES! Armor now is _[available at Packagist](https://packagist.org/packages/14mpr0gr4mm3r/armor-lib)_!

### Implemeting Armor

**CONGRATULATIONS! YOU'VE REACHED THE PROCESS FOR FIRST USE OF ARMOR!** 🎉

Jokes aside, from here you can already use Armor to handle requests received by your application.

First of all, when creating an application that uses Armor, you have to create an application instance:

```php
<?php
require "../vendor/autoload.php";

$app = new Armor\Application();
```

**Note**: Optionally, you can pass a text encoder as an argument, which will be used up front to encode the response content. By default, it uses the `utf8_encode`.

And at the bottom of the main file, put the call to the `Application::run` method:
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

**NOTE: REMEMBER ON USING THE `.htacess` file to redirect all requests to the main (`index.php`) file**

Now, when talking about handling the requests properly, we must say that Armor offers a simple and easy way to handle a request, based on the method that has been used to perform it. That is:

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

At the moment, Armor only handles GET and POST requests. But, with the support of the community, in the future, it may support more.

The callback that is passed as argument must receive two parameters: a `Request` object and a `Response` object. The `Request` object provides information about the path requested, the search-query parameters, AND... the **path parameters**. This name may not sound familiar, but if you are a back-end developer, you might have seen something like this:

```php
$app->get('/path/to/$(section)', function(Request $req, Response $res) {
    if ($req->path['section'] == 'something') {
        //do something
    }
    //...
});
```

So, Armor supports it too. But, coming back to the callback parameters, the `Response` object provides a lot of methods for appending content to the response.

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

As you can see, we are handling a request to the route `/`. We append a simple message to the response, which uses the `absolute` value of the `path` property of the `Request` object. And, finally, we complete the response, and returns it final result. That "return" is used by Armor to know if the response has been correctly built, or something unexpected has occurred. If it does, Armor throws a `ResponseCompletionNotCompletedException`.

Well, there is a lot to know about Armor. In the future, it may be fully covered by a more detailed documentation.

### A little talk about using templates

Armor offers a native extension for templates called _ArmorTemplating_. It provides two classes: `TemplateManager`, which is responsible for loading templates from their directories, and `Template`, which is the template object itself. It works like that:

```php
/** 
 * This example is a snippet taken and adapted from the 'example01', available at https://github.com/14mPr0gr4mm3r/armor-examples
 * @author 14mPr0gr4mm3r
 * @license MIT
*/

$templ = new Armor\Extensions\ArmorTemplating\TemplateManager("./", ['header', 'index']);

$app->get('/', function(Request $req, Response $res) use($templ) {
    $templ->getTemplate('index')->sendAsResponse($res);

    return $res->end();
});
```

Here, we loaded two templates: "_header.templ.armor_" and "_index.templ.armor_". They are on the same directory that the source file is. We load the manager from the inside of the closure, and use it for loading the `index` template (`getTemplate`) and sending it. For sending the template, we pass the `Response` object by reference to the `Template.sendAsResponse` method. And then, we finishes the request handling process.

Instead, to avoid adding `use` keyword to "request handling closures", we could use the `Application::use` method, like this:

```php
$templ = new Armor\Extensions\ArmorTemplating\TemplateManager("./", ['header', 'index']);

$app->use('templ', $templ);

$app->get('/', function(Request $req, Response $res) {
    $this['templ']->getTemplate('index')->sendAsResponse($res);

    return $res->end();
});
```

Yes, Armor allows you to do this. Actually, this is part of Armor's extensions service.

## Final Considerations

I admit that this was very little to be taught, but in the future, with possible more people helping, the quality and detail of the documentation might get better.

Good studies, from 14mPr0gr4mm3r (the current Armor maintainer).
