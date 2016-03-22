<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher as DataEnricher;

/**
 * Process string as Mustache template
 */
class Mustache implements Processor
{
    /**
     * @var object
     */
    protected $source;
    
    /**
     * Property key which should trigger the processor
     * @var string
     */
    protected $property;
    
    /**
     * Class constructor
     * 
     * @param DataEnricher $invoker
     * @param string       $property  Property key which should trigger the processor
     */
    public function __construct(DataEnricher $invoker, $property)
    {
        $this->source = $invoker->getSource();
        $this->property = $property;
    }
    
    /**
     * Enrich target
     * 
     * @param array|object $target
     * @return array|object
     */
    public function applyTo(&$target)
    {
        $prop = $this->property;
        
        foreach ($target as &$value) {
            if (!is_object($value) && !is_array($value)) continue;
            
            if (is_object($value) && isset($value->$prop)) {
                $value = $this->parse($value->$prop);
            } else {
                $this->applyTo($value);
            }
        }
    }
    
    /**
     * Parse as mustache template
     * 
     * @param string $template
     */
    protected function parse($template)
    {
        $mustache = new \Mustache_Engine();
        return $mustache->render($template, $this->source);
    }
}
