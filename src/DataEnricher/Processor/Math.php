<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use Hoa;

/**
 * Description of Math
 *
 * @author arnold
 */
class Math implements Processor
{
    use Processor\Implementation;

    /**
     * @var Hoa\Compiler\Llk\Parser
     */
    protected $parser;
    
    public function __construct($property)
    {
        $this->property = $property;
        
        $this->parser = Hoa\Compiler\Llk\Llk::load(
            new Hoa\File\Read('hoa://Library/Math/Arithmetic.pp')
        );
    }
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $expression = $node->getInstruction($this);
        $variables = $node->getResult() ?: [];

        $arithmetic = new Hoa\Math\Visitor\Arithmetic();
        
        foreach ($variables as $name => $value) {
            // Constants are upper case and variables lower case. We don't really care, so using a workaround.
            if (preg_match('/^[A-Z_][A-Z0-9_]*$/', $name)) {
                $arithmetic->addConstant($name, $value);
            } else {
                $arithmetic->addVariable($name, function() use ($value) { return $value; });
            }
        }
        
        $ast = $this->parser->parse($expression);
        $result = $arithmetic->visit($ast);
        
        $node->setResult($result);
    }
}
