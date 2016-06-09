<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Processor;
use LegalThings\DataEnricher\Node;
use Jasny\DotKey;

/**
 * The value will be NULL if the reference isn't set
 */
class IfSet implements Processor
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
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $ref = $node->getInstruction($this);
        
        $check = $this->source->get($ref);
        
        if (!isset($check)) {
            $node->setResult(null);
            
            foreach ($node as $prop => $value) {
                unset($node->$prop); // Remove all other properties, including processing instructions
            }
        } else {
            $result = $node->getResult();
            $result->{$this->property} = null;
            
            $node->setResult($result);
        }
    }
}
