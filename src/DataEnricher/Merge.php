<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher as DataEnricher;

/**
 * Merge multiple object into one
 */
class Merge implements Processor
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
            
            if (is_object($value) && isset($value->$prop)) $value = $this->merge($value->$prop);
            $this->applyTo($value);
        }
    }
    
    /**
     * Merge properties of an object
     * 
     * @param array $merge
     */
    protected function merge($merge)
    {
        $value = (object)[];
        
        foreach ($merge as $object) { 
            foreach ($object as $key => $value) {
               $value->$key = $value;
            }
        }
        
        return $value;
    }
}
