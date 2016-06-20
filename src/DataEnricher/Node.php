<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher\Processor;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * An object with processing instructions
 */
class Node extends \stdClass
{
    /**
     * The processed data
     * @var mixed 
     */
    protected $i_result;

    /**
     * Class constructor
     * 
     * @param \stdClass $data
     */
    public function __construct(\stdClass $data)
    {
        $this->i_result = $data;
        
        foreach ($data as $key => $value) {
            if ($key === 'i_result') {
                continue;
            }
            
            $this->$key = $value;
        }
    }

    
    /**
     * Replace nodes with their results
     * 
     * @param array|object $target
     */
    protected function applyNodeResults(&$target)
    {
        if (!is_array($target) && !is_object($target)) {
            return;
        }
        
        foreach ($target as &$value) {
            if ($value instanceof self) {
                $value = $value->getResult();
            }
            
            $this->applyNodeResults($value);
        }
    }
    
    /**
     * Get the processed result
     * 
     * @return mixed
     */
    public function getResult()
    {
        if ($this->i_result instanceof PromiseInterface) {
            $this->i_result->wait();
            
            if ($this->i_result instanceof PromiseInterface) {
                throw new \LogicException("Promise result not replaced with data");
            }
        }

        $result = $this->i_result;
        
        $this->applyNodeResults($result);
        return $result;
    }
    
    /**
     * Set the result after processing
     * 
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->i_result = $result;
    }

    
    /**
     * Test if the node has an instruction for a processor
     * 
     * @param Processor $processor
     * @return boolean
     */
    public function hasInstruction(Processor $processor)
    {
        $prop = $processor->getProperty();
        return isset($this->$prop);
    }
    
    /**
     * Get an instruction for a processor
     * 
     * @param Processor $processor
     * @return mixed
     */
    public function getInstruction(Processor $processor)
    {
        $prop = $processor->getProperty();
        
        if (!isset($this->$prop)) {
            throw new \LogicException("Node doesn't have instruction property '$prop'");
        }
        
        $value = $this->$prop;
        
        if ($value instanceof self) {
            $value = $value->getResult();
        }
        
        return $value;
    }
    
    /**
     * Apply processing to this node
     * 
     * @param Processor $processor
     */
    public function apply(Processor $processor)
    {
        if (!$this->hasInstruction($processor)) {
            return;
        }
        
        if ($this->i_result instanceof PromiseInterface) {
            $this->i_result->then(function() use ($processor) {
                $processor->applyToNode($this);
            });
        } else {
            $processor->applyToNode($this);
        }
    }
}
