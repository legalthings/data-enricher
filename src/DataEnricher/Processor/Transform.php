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
     * Default transformation functions
     * @var array
     */
    public static $defaultFunctions = [
        'hash' => 'hash',
        'base64_encode' => 'base64_encode',
        'base64_decode' => 'base64_decode',
        'json_encode' => 'json_encode',
        'json_decode' => 'json_decode',
        'serialize' => 'serialize',
        'unserialize' => 'unserialize',
        'strtotime' => 'strtotime',
        'date' => 'date'
    ];
    
    /**
     * Allowed transformation functions
     * @var array 
     */
    public $functions;
    
    
    /**
     * Class constructor
     * 
     * @param string $property  Property key with the processing instruction
     */
    public function __construct($property)
    {
        $this->property = $property;
        $this->functions = static::$defaultFunctions;
    }
    
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
            list($key, $arg) = explode(':', $transformation) + [null];
            
            if (!isset($this->functions[$key])) {
                trigger_error("Unknown transformation '$transformation'", E_USER_WARNING);
                continue;
            }
            
            $fn = $this->functions[$key];
            $value = isset($arg) ? call_user_func($fn, $arg, $value) : call_user_func($fn, $value);
        }
        
        $node->setResult($value);
    }
}
