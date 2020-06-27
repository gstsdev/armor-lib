<?php
namespace Armor\Exceptions;

class ProhibitedRouteRequestMethodException extends \Exception {
    protected $message = "Prohibited Route Request Method";
}