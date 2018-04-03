<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Join processor
 */
class Join implements Processor
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
        
        $glue = isset($instruction->glue) ? $instruction->glue : '';
        $result = join($glue, $instruction->input);
        $node->setResult($result);
    }
}
