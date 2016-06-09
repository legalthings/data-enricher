<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use LegalThings\DataEnricher\Processor\Helper;
use Mustache_Engine;

/**
 * Process string as Mustache template
 */
class Mustache implements Processor
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
        $template = $node->getInstruction($this);
        $result = $this->parse($template);
        
        $node->setResult($result);
    }
    
    /**
     * Parse as mustache template
     * 
     * @param string $template
     */
    protected function parse($template)
    {
        $data = get_object_vars($this->source) + ['@' => $this->target];
        
        $mustache = new Mustache_Engine();
        return $mustache->render($template, $data);
    }
}
