<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher\Processor;

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
     * Get the processed result
     * 
     * @return mixed
     */
    public function getResult()
    {
        return $this->i_result;
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
}
