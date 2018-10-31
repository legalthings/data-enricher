<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use LegalThings\DataEnricher\Processor\Helper;

/**
 * Choose one of the child properties based on a property in the document
 */
class SwitchChoose implements Processor
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
        $instruction = $node->getInstruction($this);

        // for bc
        if (is_string($instruction)) {
            $cases = $node->getResult();

            $result = $this->choose($instruction, $cases);
            return $node->setResult($result);
        }
        
        if (is_array($instruction) || is_object($instruction)) {
            $instruction = (object)$instruction;
        }
        
        if (!isset($instruction->on) || !isset($instruction->options)) {
            return;
        }
        
        $instruction->options = (array)$instruction->options;
        
        $result = isset($instruction->default) ? $instruction->default : null;
        
        if (isset($instruction->options[$instruction->on])) {
            $result = $instruction->options[$instruction->on];
        }
        
        $node->setResult($result);
    }
    
    /**
     * Choose on of the cases
     * 
     * @param string $ref    Property name of source
     * @param object $cases
     * @return mixed
     */
    protected function choose($ref, $cases)
    {
        $test = $this->getByReference($ref, $this->source, $this->target);
        return isset($cases->$test) ? $cases->$test : null;
    }
}
