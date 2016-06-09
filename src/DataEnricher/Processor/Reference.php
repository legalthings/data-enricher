<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Processor;
use LegalThings\DataEnricher\Processor\Helper;
use LegalThings\DataEnricher\Node;

/**
 * Symbolic link to a property of the source object
 */
class Reference implements Processor
{
    use Processor\Implementation,
        Helper\GetByReference
    {
        Helper\GetByReference::withSourceAndTarget insteadof Processor\Implementation;
    }
    
    /**
     * Apply reference processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $ref = $node->getInstruction($this);
        
        $result = $this->getByReference($ref, $this->source, $this->target);
        $node->setResult($result);
    }
}
