<?php

namespace Armor\Handle;

/**
 * The interface to the route that the user may have access to.
 * It prevents from the user trying to modify the original route object.
 */
class RouteInterface {
    private $route;

    public function __construct(Route $route) {
        $this->route =& $route;
    }

    /** Should be used to set a custom parser, beyond the ones provided by Armor */
    public function setParser(callable $parser) {
        $this->route->_addParser($parser);
    }
}