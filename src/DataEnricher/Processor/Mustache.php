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
        
        if (!is_string($template) && !is_array($template) && !is_object($template)) {
            return trigger_error("Unable to parse given template of type: " . gettype($template), E_WARNING);
        }
        
        $result = $this->getParsedResult($template);
        $node->setResult($result);
    }
    
    /**
     * Parse a template by mustache if possible and return the result
     * 
     * @param mixed $template
     *
     * @return mixed $result
     */
    protected function getParsedResult($template)
    {
        if (is_string($template)) {
            return $this->parse($template);
        } elseif (is_array($template)) {
            return array_map([$this, 'parse'], $template);
        } elseif (is_object($template)) {
            return $this->parseObject($template);
        }
        
        return $template;
    }
    
    /**
     * Parse an object with mustache
     * 
     * @param  object $template
     *
     * @return object $result
     */
    protected function parseObject($template)
    {
        $result = new \stdClass();
        
        foreach ($template as $key => $value) {
            $parsedKey = $this->parse($key);
            $parsedValue = $this->getParsedResult($value);
            $result->$parsedKey = $parsedValue;
        }
        
        return $result;
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
