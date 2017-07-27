<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * DateFormat processor, format date objects
 */
class DateFormat implements Processor
{
    use Processor\Implementation;
    
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
