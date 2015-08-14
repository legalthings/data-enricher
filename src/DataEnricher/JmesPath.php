<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher as DataEnricher;
use JmesPath\search as jmespath_search;

/**
 * JMESPath processor
 * @see http://jmespath.org/
 */
class JmesPath implements Processor
{
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
            
            if (is_object($value) && isset($value->$prop)) $value = jmespath_search($value->$prop, $value);
            $this->applyTo($value);
        }
    }
}
