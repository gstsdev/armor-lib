<?php

namespace Armor\HandlingTools;

use Exception;

$GLOBALS['__PARSERS'] = array(
    'tolower' => function($content) { return strtolower($content); },
    'toupper' => function($content) { return strtoupper($content); },
    'toint' => function($content) { return (int)$content; },
    'tobool' => function($content) { return (bool)$content; }
);

/**
 * The route object generated by the Application class and used by it. In this
 * class is where are stored the regexes, parsers and the callback of a specified
 * route.
 */
class Route {
    private $pattern, $callback;
    private $parameters, $parsers;
    private $custom_parser;

    public function __construct(string $route_pattern, array $route_params, callable $route_callback, array $parsers=array())
    {
        $this->pattern = $route_pattern;
        $this->callback = $route_callback;
        $this->parameters = $route_params;
        $this->parsers = $parsers;
    }

    /**
     * Returns if the path requested (`$pathto`) can be handled by this route object.
     * And at the same time, only if the path matches, parses the path to get the 
     * "path parameters" specified previously.
     * 
     * @param string $pathto The path requested by the user
     * @return bool TRUE if it does, FALSE if it doesn't
     */
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
                $parsers = array_filter(explode(':', $this->parsers[$key]), function($item) { return (bool)$item;});
                ///@debug print_r($parsers);
                foreach ($parsers as $parser) {
                    $parser = $parser == 'toparse' ? $this->custom_parser : $GLOBALS['__PARSERS'][$parser];
                    $this->parameters[$key] = call_user_func($parser, $this->parameters[$key]);
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

    public function _addParser(callable $parser) {
        $this->custom_parser = $parser;
    }
}