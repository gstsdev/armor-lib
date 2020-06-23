<?php

namespace Armor\Extensions\ArmorUI;

use Exception;

require_once "Template.php";

/**
 * Responsible for loading templates
 * 
 * NOTE: the templating functions are in this class.
 * It's because the **future feature** for adding custom
 * templating functions.
 *
 */
class TemplateManager {
    public $templatingFunctions;
    private $templates = array();
     
    /**
    * @param string $templates_dir Where to find the templates
    * @param array $templates_to_load Which templates to load from that directory
    */
    public function __construct(string $templates_dir, array $templates_to_load) {
        if ($templates_dir[-1] != '/') $templates_dir .= '/';

        foreach ($templates_to_load as $template) {
            $this->templates[$template] = new Template($templates_dir.$template.".templ.armor", $this);
        }

        $this->templatingFunctions = array(
            'initTemplate' => function ($template_name='') {
                if (empty($template_name)) $template_name = '_main';
            
                $template_name = ( in_array($template_name[0], ["\"", "'"]) && in_array($template_name[-1], ["\"", "'"]) )
                                 ? $template_name
                                 : '"' . $template_name . '"';
                
            
                return '$this->name = ' . $template_name . '; $this->output = function() {';
            },
        
            'endTemplate' => function() { return '};'; },
        
            'useTemplate' => function($template_name) {
                $template_name = ( in_array($template_name[0], ["\"", "'"]) && in_array($template_name[-1], ["\"", "'"]) )
                                 ? $template_name
                                 : '"' . $template_name . '"';
            
                return '$this->manager->getTemplate(' . $template_name . ')->print();';
            }
        );
    }

    /**
     * Returns the template named as `$template_name`.
     * 
     * If it doesn't exist, throws an exception
     * 
     * @throws Exception
     * @return Template
     */
    public function getTemplate(string $template_name) {
        /*if (isset($this->templates[$template_name]) && $this->templates[$template_name]->name != $template_name)
            throw new Exception("name conflict occurred on template ('{$this->templates[$template_name]->name}' found at '$template_name')");
        else*/
        if (!isset($this->templates[$template_name]))
            throw new Exception("there is not a template named '$template_name'");

        return $this->templates[$template_name];
    }

    /**
     * Add a new function that may be used from the inside of the templates
     * 
     * @param string $function_name The name of the function to be recognized at the templates
     * @param callable $function_callback The function itself, which must return a string value with valid PHP code
     * @throws Exception
     */
    public function extendFunctions(string $function_name, callable $function_callback) {
        if (array_key_exists($function_name, $this->templatingFunctions)) {
            throw new Exception("there is already a '$function_name' function");
        }

        $this->templatingFunctions[$function_name] = $function_callback;
    }
}