<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Serialize processor
 */
class Serialize implements Processor
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
     * Json serialize
     * 
     * @param mixed $input
     */
    public function json($input)
    {
        return json_encode($input);
    }

    /**
     * Url serialize
     * 
     * @param stdClass $input
     */
    public function url($input)
    {
        return http_build_query($input);
    }
}
