<?php

namespace Armor\HandlingTools;

class Route {
    private $pattern, $callback;
    private $parameters;

    public function __construct(string $route_pattern, array $route_params, callable $route_callback)
    {
        $this->pattern = $route_pattern;
        $this->callback = $route_callback;
        $this->parameters = $route_params;
    }

    public function match(string $pathto) {
        $rgx_matches = preg_match($this->pattern, $pathto, $values);

        if (!($rgx_matches) && sizeof($values) <= 0) {
            return false;
        }

        $values = array_slice($values, 1);

        for ($i = 0; $i < sizeof($values); $i++) {
            $key = array_keys($this->parameters)[$i];
            $this->parameters[$key] = $values[$i];
        }

        return $rgx_matches;
    }

    public function getCallback() { return $this->callback; }

    public function getParsedRouteParameters() {
        if (in_array(null, array_values($this->parameters))) throw new Exception("parsing parameters failed", 1);
        
        return $this->parameters;
    }
}