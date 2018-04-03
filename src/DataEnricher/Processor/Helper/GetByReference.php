<?php

namespace LegalThings\DataEnricher\Processor\Helper;

use function JmesPath\search as jmespath_search;

/**
 * Get property from source or target by reference
 */
trait GetByReference
{
    /**
     * @var object
     */
    protected $source;

    /**
     * @var object|array
     */
    protected $target;

    
    /**
     * Get copy of processor that uses the given source and target
     * 
     * @param object       $source  Data source
     * @param array|object $target  Target or dot key path
     */
    public function withSourceAndTarget($source, $target)
    {
        $clone = clone $this;
        
        $clone->source = $source;
        $clone->target = $target;
        
        return $clone;
    }
    
    /**
     * Get item by reference
     * 
     * @param string       $ref
     * @param object       $source
     * @param object|array $target
     */
    protected function getByReference($ref, $source, $target)
    {
        $subject = $source;
        
        $result = jmespath_search($ref, $subject);
        
        return $result;
    }
}
