<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Processor;
use LegalThings\DataEnricher\Processor\Helper;
use LegalThings\DataEnricher\Node;

/**
 * The value will be NULL if the reference isn't set
 */
class IfSet implements Processor
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
        $check = $this->getByReference($ref, $this->source, $this->target);
        
        if (!isset($check)) {
            $node->setResult(null);
            
            foreach ($node as $prop => $value) {
                unset($node->$prop); // Remove all other properties, including processing instructions
            }
        } else {
            $result = $node->getResult();
            $result->{$this->property} = null;
            
            $node->setResult($result);
        }
    }
}
