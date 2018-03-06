<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Unserialize processor
 */
class Unserialize implements Processor
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
        
        if (is_array($instruction) || is_object($instruction)) {
            $instruction = (object)$instruction;
        }
        
        if (!isset($instruction) || !isset($instruction->input) || !isset($instruction->format)) {
            return;
        }
        
        if (!method_exists($this, $instruction->format)) {
            return;
        }
        
        $result = call_user_func_array([$this, $instruction->format], [$instruction->input]);
        
        $node->setResult($result);
    }
    
    
    /**
     * Json unserialize
     * 
     * @param string $input
     */
    public function json($input)
    {
        return json_decode($input);
    }

    /**
     * Url unserialize
     * 
     * @param string $input
     */
    public function url($input)
    {
        parse_str($input, $output);
        return (object)$output;
    }
}
