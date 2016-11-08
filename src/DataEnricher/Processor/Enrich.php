<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Enriches objects in arrays by matching them
 */
class Enrich implements Processor
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
        $source = $node->getResult() ?: [];
        
        if (is_string($instruction->match)) {
            $match = ['extra' => $instruction->match, 'source' => $instruction->match];
        } else {
            $match = (array)$instruction->match;
        }
        
        $extraIndexed = [];
        
        foreach ($instruction->extra as $extra) {
            $key = \Jasny\DotKey::on($extra)->get($match['extra']);

            if (!isset($key) || isset($extraIndexed[$key])) {
                continue;
            }
            
            if (!is_scalar($key)) {
                trigger_error("Trying to match on non-scalar type", E_WARNING);
                continue;
            }
            
            $extraIndexed[$key] = $extra;
        }
        
        foreach ($source as &$item) {
            $key = \Jasny\DotKey::on($item)->get($match['source']);

            if (!isset($key)) {
                continue;
            }
            
            if (!is_scalar($key)) {
                trigger_error("Trying to match on non-scalar type", E_WARNING);
                continue;
            }
            
            if (!isset($extraIndexed[$key])) {
                continue;
            }
            
            $item = array_merge((array)$item, (array)$extraIndexed[$key]);
        }
        
        $node->setResult($source);
    }
}
