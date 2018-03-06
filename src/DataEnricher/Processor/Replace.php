<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Replace processor
 */
class Replace implements Processor
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
        
        if (is_array($instruction) || is_object($instruction)) {
            $instruction = (object)$instruction;
        }
        
        if (!isset($instruction) || !isset($instruction->input) || !isset($instruction->replacement)) {
            return;
        }
        
        if (!isset($instruction->find) && !isset($instruction->regex)) {
            return;
        }
        
        if (isset($instruction->find)) {
            $result = str_replace($instruction->find, $instruction->replacement, $instruction->input);
        } else {
            $result = preg_replace($instruction->regex, $instruction->replacement, $instruction->input);
        }
        
        $node->setResult($result);
    }
}
