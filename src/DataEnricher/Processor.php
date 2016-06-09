<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Node;

/**
 * A processor does a single type of data enrichment
 */
interface Processor
{
    /**
     * Class constructor
     * 
     * @param DataEnricher $invoker
     * @param string       $property  Property key which should trigger the processor
     */
    public function __construct(DataEnricher $invoker, $property);

    /**
     * Get the property key that holds the processing instruction
     * 
     * @return string
     */
    public function getProperty();
    
    /**
     * Prepare processing for nodes
     * 
     * @param Node[] $nodes
     */
    public function prepare(array $nodes);
    
    /**
     * Apply processing to a node
     * @param Node $node
     */
    public function applyToNode(Node $node);
}
