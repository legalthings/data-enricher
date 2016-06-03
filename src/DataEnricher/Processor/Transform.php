<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Transform processor, apply transformation functions on data
 */
class Transform implements Processor
{
    use Processor\Implementation;
    
    /**
     * Allowed transformation functions
     * @var type 
     */
    public $allowed = [
        'hash',
        'base64_encode',
        'base64_decode',
        'json_encode',
        'json_decode',
        'serialize',
        'unserialize'
    ];
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $transformations = (array)$node->getInstruction($this);
        
        if (!isset($node->input)) {
            return;
        }
        
        $value = $node->input;
        
        if ($value instanceof Node) {
            $value = $value->getResult();
        }
        
        foreach ($transformations as $transformation) {
            list($fn, $arg) = explode(':', $transformation) + [null];
            
            if (!in_array($fn, $this->allowed)) {
                trigger_error("Unknown transformation '$transformation'", E_USER_WARNING);
                continue;
            }
            
            $value = isset($arg) ? $fn($arg, $value) : $fn($value);
        }
        
        $node->setResult($value);
    }
}
