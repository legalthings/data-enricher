<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Transform processor, apply transformation functions on data
 */
class DateFormat implements Processor
{
    use Processor\Implementation;
    
    /**
     * Class constructor
     * 
     * @param string $property  Property key with the processing instruction
     */
    public function __construct($property)
    {
        $this->property = $property;
    }
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $instruction = $node->getInstruction($this);
        
        if(!isset($instruction->date)) {
            return;
        }
        
        $format = 'Y-m-d';
        if (isset($instruction->format)) {
            $format = $instruction->format;
        }
        
        $date = new \DateTime($instruction->date);
        if (isset($instruction->timezone)) {
            $date = new \DateTime($instruction->date, new \DateTimeZone($instruction->timezone));
        }
        
        $result = $date->format($format);
        
        $node->setResult($result);
    }   
}
