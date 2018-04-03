<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use StephenHill\Base58;

/**
 * Hash processor
 */
class Hash implements Processor
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
        
        $hmac = isset($instruction->hmac) ? $instruction->hmac : null;
        $result = call_user_func_array([$this, $instruction->algo], [$instruction->input, $hmac]);
        
        $node->setResult($result);
    }
    
    
    /**
     * md5 hash
     * 
     * @param string $input
     * @param string $hmac
     */
    public function md5($input, $hmac = null)
    {
        return $hmac ?
            hash_hmac('md5', $input, $hmac) :
            hash('md5', $input);
    }
    
    /**
     * sha1 hash
     * 
     * @param string $input
     * @param string $hmac
     */
    public function sha1($input, $hmac = null)
    {
        return $hmac ?
            hash_hmac('sha1', $input, $hmac) :
            hash('sha1', $input);
    }
    
    /**
     * sha256 hash
     * 
     * @param string $input
     * @param string $hmac
     */
    public function sha256($input, $hmac = null)
    {
        return $hmac ?
            hash_hmac('sha256', $input, $hmac) :
            hash('sha256', $input);
    }
    
    /**
     * sha512 hash
     * 
     * @param string $input
     * @param string $hmac
     */
    public function sha512($input, $hmac = null)
    {
        return $hmac ?
            hash_hmac('sha512', $input, $hmac) :
            hash('sha512', $input);
    }
    
    /**
     * crc32 hash
     * 
     * @param string $input
     * @param string $hmac
     */
    public function crc32($input, $hmac = null)
    {
        return $hmac ?
            hash_hmac('crc32', $input, $hmac) :
            hash('crc32', $input);
    }
}
