<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Equal processor
 */
class Equal implements Processor
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
        
        if (!is_array($instruction) || count($instruction) !== 2) {
            $node->setResult(false);
        }
        
        // might want to improve this check for different types using a library
        $node->setResult($instruction[0] == $instruction[1]);
    }
}
