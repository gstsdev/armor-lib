<?php

namespace Armor\Handle;

/**
 * The interface to the route the user just defined.
 * 
 * This prevents the user from trying to modify the original route object.
 * 
 */
class RouteInterface {
    /**
     * A reference to the route that this object is "linked to".
     * 
     * @var Route
     */
    private $route;

    /**
     * @param Route $route A reference to the route that this object is "linked to".
     */
    public function __construct(Route $route) {
        $this->route =& $route;
    }

    /**
     * Should be used to set a custom parser, beyond the ones provided by Armor.
     * 
     * An alias for `Route#_addParser`.
     * 
     * @param \callable $parser The function that will parse a route parameter.
     */
    public function setParser(callable $parser) {
        $this->route->_addParser($parser);
    }
}