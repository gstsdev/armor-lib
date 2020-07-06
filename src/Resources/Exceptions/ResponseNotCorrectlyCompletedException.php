<?php
namespace Armor\Exceptions;

/**
 * This exception is usually thrown when the method `Response#end` is
 * not called and not returned at the end of the route callback.
 * 
 * @property string $message
 */
class ResponseNotCorrectlyCompletedException extends \Exception {
    protected $message = "Response was not correctly built";
}