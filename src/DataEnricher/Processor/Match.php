<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Match processor
 */
class Match implements Processor
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
        
        if (!isset($instruction) || !isset($instruction->input)) {
            return;
        }
        
        if (!isset($instruction->find) && !isset($instruction->regex)) {
            return;
        }
        
        if (isset($instruction->find)) {
            $result = strpos($instruction->input, $instruction->find) !== false;
        } else {
            $flags = isset($instruction->flags) ? $instruction->flags : 0;
            $result = preg_match($instruction->regex, $instruction->input, $matches, $flags);
        }
        
        $node->setResult($result);
    }
}
