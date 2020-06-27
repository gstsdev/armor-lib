<?php
namespace Armor\Exceptions;

class ResponseNotCorrectlyCompletedException extends \Exception {
    protected $message = "Response was not correctly built";
}