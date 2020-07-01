<?php

namespace Armor\Handle;

use Exception;
use TypeError;

/**
 * The representation of the response to be sent to the
 * user.
 */
class Response {
    private $responseConstructors = array();
    private $encoder;
    const JSON_PARSE = 0;

    /**
     * @todo Extend the constants to cover the most used headers types
     */
    const HEADER_REDIRECT = 10;
    const HEADER_CONTENT_TYPE = 11;
    const HEADER_CONTENT_ENCODE = 12;

    public function __construct($encoder=null)
    {
        $this->encoder = $encoder ? $encoder : function($data) { return utf8_encode($data); };
    }

    /**
     * Loads (and returns) the content from a HTTP page or a local file
     * @param string $pathto The path to the file
     * @param callback $parser (optional) A function to parse the content
     * @return string|object Content of the requested file (parsed or not)
     */
    public static function loadContentFrom($pathto, $parser=null) {
        $content = file_get_contents($pathto);
        
        if ($parser && is_integer($parser)) {
            switch($parser) {
                case 0:
                    $parser = function($data) { return json_decode($data, true); };
                    break;
                default:
                    break;
            }
        }

        if ($parser) {
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
     * @param string|callable $constructor
     * @return bool
     */
    public function append($constructor) {
        if (is_callable($constructor)) {
            array_push($this->responseConstructors, $constructor);
            return true;
        } elseif (gettype($constructor) == "string") {
            array_push($this->responseConstructors, function() use($constructor) { return $constructor; });
            return true;
        }

        return false;
    }

    /**
     * It's a self implementation of native "header" function.
     * 
     * @param string $headername The header to be set. It allows the use of constants 
     * aiming a more easy functionality.
     * @param string|int $headervalue The value to set on the header. 
     */
    public function setHeader($headername, $headervalue) {
        $headername = $this->getHeaderIdentifier($headername);

        array_unshift($this->responseConstructors, function() use($headername, $headervalue) { header("$headername: $headervalue"); });
    }

    /**
     * Parses the header name passed to `setHeader`.
     * 
     * @return string
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
     * Finishes the response, appending a
     * last content to it
     * 
     * @param string $finalContent An optional content to add at bottom of the (response) page. Default is empty ("").
     * @param int $finalResponseCode An optional response code to set. Default is 200.
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