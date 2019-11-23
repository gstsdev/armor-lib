<?php
namespace Armor\Exceptions;

use Exception;

class ResponseCompletionNotCompletedException extends Exception {
    protected $message = "Response was not correctly built";
}