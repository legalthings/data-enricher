<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Merge multiple object into one
 */
class Merge implements Processor
{
    use Processor\Implementation;
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $list = $node->getInstruction($this);
        
        $result = $this->merge($list);
        $node->setResult($result);
    }
    
    /**
     * Merge properties of an object
     * 
     * @param array $merge
     */
    protected function merge($merge)
    {
        $value = (object)[];
        
        foreach ($merge as $object) { 
            foreach ($object as $key => $value) {
               $value->$key = $value;
            }
        }
        
        return $value;
    }
}
