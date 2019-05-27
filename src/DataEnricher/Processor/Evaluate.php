<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use function JmesPath\search as jmespath_search;

/**
 * Process JMESPath compatible expression
 * @see http://jmespath.org/
 */
class Evaluate implements Processor
{
    use Processor\Implementation,
        Helper\GetByReference
    {
        Helper\GetByReference::withSourceAndTarget insteadof Processor\Implementation;
    }

    /**
     * Apply processing to a single node
     *
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $instruction = $node->getInstruction($this);

        $result = jmespath_search($instruction, $this->source);

        $node->setResult($result);
    }
}
