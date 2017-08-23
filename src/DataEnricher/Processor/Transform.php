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
        'hash_hmac' => 'hash_hmac',
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
        $instruction = $node->getInstruction($this);
        $transformations = !is_array($instruction) ? [$instruction] : $instruction;
        
        if (isset($node->input)) {
            $input = $this->resolveNodes($node->input);
        }
        
        foreach ($transformations as $transformation) {
            if (is_string($transformation) && !isset($input)) {
                continue;
            }
            
            if (is_string($transformation)) {
                list($key, $args) = explode(':', $transformation) + [null, null];
            } elseif (is_object($transformation) || is_array($transformation)) {
                $transformation = (object)$transformation;
                $key = isset($transformation->function) ? $transformation->function : null;
                $args = isset($transformation->args) ? $transformation->args : [];
            }
            
            if (!isset($this->functions[$key])) {
                throw new \Exception("Unknown transformation '$key'");
            }
            
            if (isset($args)) {
                $args = $this->resolveNodes($args);
            }
            
            $fn = $this->functions[$key];
            
            if (is_string($transformation)) {
                $result = isset($args) ? call_user_func($fn, $args, $input) : call_user_func($fn, $input);
            } elseif (is_object($transformation)) {
                $result = call_user_func_array($fn, $args);
            }
        }
        
        if (isset($result)) {
            $node->setResult($result);
        }
    }
    
    /**
     * Resolve instructions of nodes by getting their results
     * 
     * @param Node $node
     * 
     * @return mixed $result
     */
    protected function resolveNodes($node) {
        if (!$node instanceof Node) {
            return $node;
        }

        return $node->getResult();
    }
}
