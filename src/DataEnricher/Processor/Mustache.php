<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use Mustache_Engine;

/**
 * Process string as Mustache template
 */
class Mustache implements Processor
{
    use Processor\Implementation;
    
    /**
     * @var object
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
        $this->source = $invoker->getSource();
        $this->property = $property;
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
        $mustache = new Mustache_Engine();
        return $mustache->render($template, $this->source);
    }
}
