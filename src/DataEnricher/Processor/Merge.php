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
        $instruction = $node->getInstruction($this);
        
        $list = $this->resolve($instruction);
        
        $result = $this->merge($list);
        $node->setResult($result);
    }
    
    /**
     * Resolve processing nodes in the instruction
     * 
     * @param array $list
     * @return array
     */
    public function resolve($list)
    {
        if ($list instanceof Node) {
            $list = $list->getResult();
        }
        
        foreach ($list as &$item) {
            if ($item instanceof Node) {
                $item = $item->getResult();
            }
        }
        
        return $list;
    }
    
    /**
     * Merge properties of an object
     * 
     * @param array $list
     * @return \stdClass|array
     */
    protected function merge(array $list)
    {
        foreach ($list as &$item) {
            if (is_object($item)) {
                $item = get_object_vars($item);
            }
        }
        
        $result = call_user_func_array('array_merge', $list);
        
        // Is associative array
        if (array_keys($result) !== array_keys(array_keys($result))) {
            $result = (object)$result;
        }
        
        return $result;
    }
}
