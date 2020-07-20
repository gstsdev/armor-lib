<?php

namespace Armor\Handle;

use \Armor\Handle\ExtensibleObject;

use Exception;
use TypeError;

function _is_valid_resource_path(string $path) {
    $_is_http_resource = (
        substr($path, 0, strlen("http://")) == "http://"
        ||
        substr($path, 0, strlen("https://")) == "https://"
    );

    return $_is_http_resource || is_file($path);
}

/**
 * The representation of the response to be sent to the
 * user.
 * 
 * @see \Armor\Handle\ExtensibleObject
 */
class Response extends ExtensibleObject {
    /** 
     * The functions that are used to build the final response content 
     * 
     * @var \callable[]
     */
    private $responseConstructors = array();
    /** 
     * The encoder of the response. Default is `utf8_encode`.
     * 
     * @var \callable
     */
    private $encoder;

    const JSON_PARSE = 0;

    /**
     * @todo Extend the constants to cover the most used headers types
     */
    const HEADER_REDIRECT = 10;
    const HEADER_CONTENT_TYPE = 11;
    const HEADER_CONTENT_ENCODE = 12;

    /**
     * @param \callable|null $encoder The encoder of the response. Default is `utf8_encode`.
     */
    public function __construct($encoder=null)
    {
        parent::__construct();
        $this->encoder = $encoder !== null ? $encoder : function($data) { return utf8_encode($data); };
    }

    /**
     * Loads (and returns) the content from a HTTP page or a local file.
     * 
     * @param \string $pathto The path to the file
     * @param \callable $parser (optional) A function to parse the content
     * @return \string|\object|\null Content of the requested file (parsed or not)
     */
    public static function loadContentFrom($pathto, $parser=null) {
        if (!_is_valid_resource_path($pathto))
            return null;

        $content = file_get_contents($pathto);
        
        if ($parser !== null && is_integer($parser)) {
            switch($parser) {
                case 0:
                    $parser = function($data) { return json_decode($data, true); };
                    break;
                default:
                    break;
            }
        }

        if ($parser !== null) {
            if (is_callable($parser)) {
                $content = call_user_func($parser, $content);
            } else {
                throw new TypeError("Parser must be callable, not {gettype($parser)}", 1);
            }
        }
        
        return $content;
    }

    /**
     * Append some content to the response.
     * 
     * @param \string|\callable $constructor
     * @return \bool
     */
    public function append($constructor) {
        if (is_callable($constructor)) {
            array_push($this->responseConstructors, $constructor);
            return true;
        } elseif (is_string($constructor)) {
            array_push($this->responseConstructors, function() use($constructor) { return $constructor; });
            return true;
        }

        return false;
    }

    /**
     * It's a self implementation of native "header" function.
     * 
     * @param \string $headername The header to be set. It allows the use of constants 
     * aiming a more easy functionality.
     * @param \string|\int $headervalue The value to set on the header. 
     */
    public function setHeader($headername, $headervalue) {
        $headername = $this->getHeaderIdentifier($headername);

        array_unshift(
            $this->responseConstructors, 
            function() use($headername, $headervalue) {
                header("$headername: $headervalue");
            }
        );
    }

    /**
     * Parses the header name passed to `setHeader`.
     * 
     * @param \string|\int $code
     * @return \string
     */
    private function getHeaderIdentifier($code) {
        if (is_int($code)) {
            switch($code) {
                case 10:
                    return "Content-Location";
                case 11:
                    return "Content-Type";
                case 12:
                    return "Content-Encoding";
                default:
                    throw new Exception("invalid \"HEADER_\" constant", 1);
            }
        } elseif (is_string($code)) {
            return $code;
        }
    }

    /**
     * Finishes the response, appending a last content to it.
     * 
     * @param \string $finalContent An optional content to add at bottom of the (response) page. Default is empty ("").
     * @param \int $finalResponseCode An optional response code to set. Default is 200.
     */
    public function end($finalContent="", $finalResponseCode=200) {
        http_response_code($finalResponseCode);

        $finalResponse = "";

        foreach ($this->responseConstructors as $constructor) {
            $finalResponse .= call_user_func($constructor)."\n";
        }

        $finalResponse .= $finalContent;

        $finalResponse = call_user_func($this->encoder, $finalResponse);

        echo $finalResponse;

        return true;
    }
}