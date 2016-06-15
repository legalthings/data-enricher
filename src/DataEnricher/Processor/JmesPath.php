<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use function JmesPath\search as jmespath_search;
use JmesPath\Utils;

/**
 * JMESPath processor
 * @see http://jmespath.org/
 */
class JmesPath implements Processor
{
    use Processor\Implementation;
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $path = $node->getInstruction($this);
        $input = $node->getResult();
        
        $result = jmespath_search($path, $input);
        static::objectivy($result);
        
        $node->setResult($result);
    }
    
    /**
     * Cast associated arrays to objects
     * 
     * @return mixed
     */
    protected static function objectivy(&$value)
    {
        if (Utils::isObject($value)) {
            $value = (object)$value;
        }
        
        if (is_array($value) || is_object($value)) {
            foreach ($value as &$item) {
                static::objectivy($item);
            }
        }
    }
}
