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
     * Apply processer to target
     * 
     * @param object|array $target
     */
    public function applyTo(&$target);
}
