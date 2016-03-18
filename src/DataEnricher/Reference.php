<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher as DataEnricher;
use Jasny\DotKey;

/**
 * Symbolic link to a property of the source object
 */
class Reference implements Processor
{
    /**
     * @var DotKey
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
        $this->source = DotKey::on($invoker->getSource());
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
            if (is_object($value) && isset($value->$prop)) {
                $value = $this->source->get($value->$prop);
            }
            
            if (is_object($value) || is_array($value)) {
                $this->applyTo($value);
            }
        }
    }
}
