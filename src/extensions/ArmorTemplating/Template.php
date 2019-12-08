<?php

namespace Armor\Extensions\ArmorTemplating;

class Template {
    public $name, $output;
    private $content;
    private $manager;

    public function __construct($content, &$manager) {
        if (is_file($content) && substr($content, -12) == ".templ.armor") {
            $content = file_get_contents($content);
        }

        $this->content = $content;
        $this->manager =& $manager;
    }

    /**
     * Receives a `Response` object by reference and use it
     * for sending its final content
     * 
     * @param Response &$response_object The `Response` object (by reference)
     * 
     */
    public function sendAsResponse(&$response_object) {
        $this->parseAndApply();
        $response_object->append($this->output);
    }
    
    public function print() {
        $this->parseAndApply();
        call_user_func($this->output);
    }

    private function parseAndApply($debug="") {
        $parsed = $this->getParsed();
        //print(strtoupper($debug) . ": ". str_replace('<', '&lt;', str_replace('>', '&gt;', $parsed))."<br><br>");
        eval('?>'.$parsed.'<?php ');
        return $this;
    }

    public function getParsed() {
        $parsed = $this->content;
        $tokens = $this->_tokenize();

        $filteredTokens = array_filter($tokens, create_function('$item', 'return substr($item, 0, 2) == "<%" && substr($item, -2) == "%>";'));

        foreach ($filteredTokens as $token) {
            $code = trim(substr($token, 2, -2));
            $replacement = "";

            if (preg_match("/^(\w+)\((.*?)\)$/", $code, $func_matches)) {
                list($_, $funcname, $funcargs) = $func_matches;
                $result = call_user_func_array($this->manager->templatingFunctions[$funcname], explode(',', $funcargs));
                if ($result) $replacement = "<?php $result ?>";
            } elseif ($code[0] == '=') {
                $val = trim(substr($code, 1));
                $replacement = "<?php echo $val; ?>";
            } else {
                $replacement = "<?php $code ?>";
            }

            $parsed = str_replace($token, $replacement, $parsed);
        }

        return $parsed;
    }

    private function _tokenize() {
        $chars = str_split($this->content);

        $tokens = [];
        $tok = "";
        $snippet = false;

        for ($i=0; $i < count($chars); $i++) {

            $char = $chars[$i];

            if ($tok == '<%') {
                $snippet = true;
            }

            if (substr($tok, -2) == "<%") {
                array_push($tokens, substr($tok, 0, -2));
                $tok = substr($tok, -2);
            }

            if (substr($tok, -2) == '%>') {
                if (!empty($tok)) 
                    array_push($tokens, $tok);
                $tok = "";
                $snippet = false;
            }

            if (($char == ' ' || $char == "\n") && $snippet == false) {
                if (!empty($tok))
                    array_push($tokens, $tok);
                $tok = "";
                continue;
            }

            $tok .= $char;
        }

        if (!empty($tok))
            array_push($tokens, $tok);

        return $tokens;
    }
}