<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * IfElse processor
 */
class IfElse implements Processor
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
        
        if (!isset($instruction) || !isset($instruction->condition)) {
            return;
        }
        
        if ($instruction->condition) {
            $result = isset($instruction->then) ? $instruction->then : $node->getResult();
        } else {
            $result = isset($instruction->else) ? $instruction->else : null;
        }
        
        $node->setResult($result);
    }
}
