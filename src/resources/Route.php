<?php

namespace Armor\HandlingTools;

define('PARSERS', array(
    'tolower' => create_function('$content', 'return strtolower($content);'),
    'toupper' => create_function('$content', 'return strtoupper($content);'),
    'toint' => create_function('$content', 'return (int)$content;'),
    'tobool' => create_function('$content', 'return (bool)$content;')
));

class Route {
    private $pattern, $callback;
    private $parameters, $parsers;

    public function __construct(string $route_pattern, array $route_params, callable $route_callback, array $parsers=array())
    {
        $this->pattern = $route_pattern;
        $this->callback = $route_callback;
        $this->parameters = $route_params;
        $this->parsers = $parsers;
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
            if (array_key_exists($key, $this->parsers)) {
                $parsers = array_filter(explode(':', $this->parsers), create_function('$item', 'return (bool)$item;'));
                foreach ($parsers as $parser) {
                    $this->parameters[$key] = call_user_func(PARSERS[$parser], $this->parameters[$key]);
                }
            }
        }

        return $rgx_matches;
    }

    public function getCallback() { return $this->callback; }

    public function getParsedRouteParameters() {
        if (in_array(null, array_values($this->parameters))) throw new Exception("parsing parameters failed", 1);
        
        return $this->parameters;
    }
}