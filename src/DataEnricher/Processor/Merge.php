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
        
        if (isset($list) && !is_array($list)) {
            $type = (is_object($list) ? get_class($list) . ' ' : '') . gettype($list);
            throw new \Exception("Unable to apply {$this->property}: Expected an array, got a $type");
        }
        
        $result = $this->merge((array)$list);
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
            
            if ($item instanceof \Traversable) {
                $item = iterator_to_array($item);
            }
        }
        
        return $list;
    }
    
    /**
     * Merge properties of an object
     * 
     * @param array $list
     * @return \stdClass|array|string
     */
    protected function merge(array $list)
    {
        if (empty($list)) {
            return null;
        }
        
        $scalar = false;
        
        foreach ($list as $key => &$item) {
            if (!isset($item)) {
                unset($list[$key]);
                continue;
            }
            
            if (is_object($item)) {
                $item = get_object_vars($item);
            }

            if ($scalar && !is_scalar($item)) {
                throw new \Exception("Unable to apply {$this->property}: Mixture of scalar and non-scalar values");
            }
            
            $scalar = is_scalar($item);
        }

        if ($scalar) {
            $result = join('', $list);
        } else {
            $result = call_user_func_array('array_merge', $list);

            // Is associative array
            if (array_keys($result) !== array_keys(array_keys($result))) {
                $result = (object)$result;
            }
        }
        
        return $result;
    }
}

