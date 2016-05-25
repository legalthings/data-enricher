<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use Jasny\DotKey;

/**
 * Choose one of the child properties based on a property in the document
 */
class SwitchChoose implements Processor
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
    public function applyToNode($node)
    {
        $ref = $node->getInstruction($this);
        $cases = $node->getResult();
        
        $result = $this->choose($ref, $cases);
        $node->setResult($result);
    }
    
    /**
     * Choose on of the cases
     * 
     * @param string $ref    Property name of source
     * @param object $cases
     * @return type
     */
    protected function choose($ref, $cases)
    {
        $test = $this->source->get($ref);
        return isset($cases->$test) ? $cases->$test : null;
    }
}
