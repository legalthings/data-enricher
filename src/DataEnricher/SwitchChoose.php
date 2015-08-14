<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher as DataEnricher;
use Jasny\DotKey;

/**
 * Choose one of the child properties based on a property in the document
 */
class SwitchChoose implements Processor
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
            if (!is_object($value) && !is_array($value)) continue;
            
            if (is_object($value) && isset($value->$prop)) $value = $this->choose($value->$prop, $value);
            $this->applyTo($value);
        }
    }
    
    /**
     * Choose on of the cases
     * 
     * @param string $ref    Property name of source
     * @param object $cases
     * @return type
     */
    protected function choose($ref, $cases)
    {
        $test = $this->source->get($ref);
        return isset($cases->$test) ? $cases->$test : null;
    }
}
