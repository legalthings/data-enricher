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
            throw new \Exception("Unable to apply {$this->property} processing instruction:"
                . " Expected an array, got a " . (is_object($list) ? get_class($list) . ' ' : '') . gettype($list));
        }
        
        $result = $this->execute((array)$list);
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
        
        if (is_array($list)) {
            foreach ($list as &$item) {
                if ($item instanceof Node) {
                    $item = $item->getResult();
                }

                if ($item instanceof \Traversable) {
                    $item = iterator_to_array($item);
                }
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
    protected function execute(array $list)
    {
        if (empty($list)) {
            return null;
        }
        
        $scalar = [];
        
        foreach ($list as $key => &$item) {
            if (!isset($item)) {
                unset($list[$key]);
                continue;
            }
            
            if (is_object($item)) {
                $item = get_object_vars($item);
            }
            
            $scalar[] = is_scalar($item);
        }

        if (count(array_unique($scalar)) > 1) {
            throw new \Exception("Unable to apply {$this->property} processing instruction:"
                . " Mixture of scalar and non-scalar values");
        }
        
        if (count($scalar) > 0 && $scalar[0]) {
            $result = join('', $list);
        } else {
            if (empty($list)) {
                return null;
            }
            $result = call_user_func_array('array_merge', $list);
            
            // Is associative array
            if (array_keys($result) !== array_keys(array_keys($result))) {
                $result = (object)$result;
            }
        }
        
        return $result;
    }
}

