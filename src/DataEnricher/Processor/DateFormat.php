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
        
        if(!isset($instruction->date) && !isset($instruction->input)) {
            return;
        }
        
        $format = 'Y-m-d';
        if (isset($instruction->format)) {
            $format = $instruction->format;
        }
        
        $input = isset($instruction->input) ? $instruction->input : $instruction->date;
        $date = new \DateTime($input);
        if (isset($instruction->timezone)) {
            $date = new \DateTime($input, new \DateTimeZone($instruction->timezone));
        }
        
        $result = $date->format($format);
        
        $node->setResult($result);
    }   
}
