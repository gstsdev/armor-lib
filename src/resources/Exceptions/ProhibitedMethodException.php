<?php
namespace Armor\Exceptions;

use Exception;

class ProhibitedMethodException extends Exception {
    protected $message = "Prohibited Method";
}