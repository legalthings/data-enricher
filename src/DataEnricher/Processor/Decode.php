<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use StephenHill\Base58;

/**
 * Decode processor
 */
class Decode implements Processor
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
        
        if (!isset($instruction) || !isset($instruction->input) || !isset($instruction->algo)) {
            return;
        }
        
        if (!method_exists($this, $instruction->algo)) {
            return;
        }
        
        $result = call_user_func_array([$this, $instruction->algo], [$instruction->input]);
        
        $node->setResult($result);
    }
    
    
    /**
     * Base64 decode
     * 
     * @param string $input
     */
    public function base64($input)
    {
        return base64_decode($input);
    }
    
    /**
     * Base58 decode
     * 
     * @param string $input
     */
    public function base58($input)
    {
        $base58 = new Base58();
        return $base58->decode($input);
    }
    
    /**
     * Url decode
     * 
     * @param string $input
     */
    public function url($input)
    {
        return urldecode($input);
    }
}
