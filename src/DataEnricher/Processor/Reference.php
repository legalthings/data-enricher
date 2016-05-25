<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Processor;
use LegalThings\DataEnricher\Node;
use Jasny\DotKey;

/**
 * Symbolic link to a property of the source object
 */
class Reference implements Processor
{
    use Processor\Implementation;
    
    /**
     * @var DotKey
     */
    protected $source;
    
    /**
     * Class constructor
     * 
     * @param DataEnricher $invoker
     * @param string       $property  Property key which should trigger the processor
     */
    public function __construct(DataEnricher $invoker, $property)
    {
        $this->source = DotKey::on($invoker->getSource());
        $this->property = $property;
    }
    
    /**
     * Apply reference processing to a single node
     * 
     * @param Node $node
     */
    protected function applyToNode(Node $node)
    {
        $ref = $node->getInstruction($this);
        
        $result = $this->source->get($ref);
        $node->setResult($result);
    }
}
