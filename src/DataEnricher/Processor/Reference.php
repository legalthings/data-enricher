<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Reference JMESPath processor
 * @see http://jmespath.org/
 */
class Reference implements Processor
{
    use Processor\Implementation,
        Helper\GetByReference
    {
        Helper\GetByReference::withSourceAndTarget insteadof Processor\Implementation;
    }
    
    /**
     * Apply processing to a single node
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
