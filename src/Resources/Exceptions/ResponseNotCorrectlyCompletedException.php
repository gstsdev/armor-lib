<?php
namespace Armor\Exceptions;

use Exception;

class ResponseNotCorrectlyCompletedException extends Exception {
    protected $message = "Response was not correctly built";
}