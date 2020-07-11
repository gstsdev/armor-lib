<?php
namespace Armor\Exceptions;

/**
 * This exception is thrown when route methods that are not
 * implemented are used by the framework users.
 * 
 * @property string $message
 */
class ProhibitedRouteRequestMethodException extends \Exception {
    protected $message = "Prohibited Route Request Method";
}