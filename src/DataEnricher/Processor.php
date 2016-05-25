<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher as DataEnricher;

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
     * Apply processing to nodes
     * 
     * @param Node[] $nodes
     */
    public function applyTo(array $nodes);
}
