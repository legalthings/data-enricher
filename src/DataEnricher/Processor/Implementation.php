<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Node;

/**
 * Basic stuff for a processor implementation
 */
trait Implementation
{
    /**
     * Property key which should trigger the processor
     * @var string
     */
    protected $property;
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     * @return void
     */
    abstract protected function applyToNode(Node $node);
    
    /**
     * Class constructor
     * 
     * @param DataEnricher $invoker
     * @param string       $property  Property key with the processing instruction
     */
    public function __construct(DataEnricher $invoker, $property)
    {
        $this->property = $property;
    }
    
    /**
     * Get the property key that holds the processing instruction
     * 
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }
    
    /**
     * Apply reference processing
     * 
     * @param Node[] $nodes
     */
    public function applyTo(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node->hasInstruction($this)) {
                $this->applyToNode($node);
            }
        }
    }
}
