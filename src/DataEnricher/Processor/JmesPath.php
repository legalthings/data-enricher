<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use JmesPath\search as jmespath_search;

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
        $node->setResult($result);
    }
}
