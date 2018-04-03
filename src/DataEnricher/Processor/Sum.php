<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Calculate sum of the numbers in an array
 */
class Sum implements Processor
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
     * Calculate sum
     * 
     * @param array $list
     * 
     * @return number
     */
    protected function execute(array $list)
    {
        $sum = array_sum($list);
        
        return $sum;
    }
}

