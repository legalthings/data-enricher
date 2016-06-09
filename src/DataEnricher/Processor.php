<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher\Node;

/**
 * A processor does a single type of data enrichment
 */
interface Processor
{
    /**
     * Class constructor
     * 
     * @param string $property  Property key with the processing instruction
     */
    public function __construct($property);

    /**
     * Get the property key that holds the processing instruction
     * 
     * @return string
     */
    public function getProperty();
    
    /**
     * Apply processing to a node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node);
}
