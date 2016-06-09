<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;

/**
 * Load an external source
 */
class Http implements Processor
{
    /**
     * Property key which should trigger the processor
     * @var string
     */
    protected $property;
    
    /**
     * @var \SplObjectStorage|Promise\PromiseInterface[]
     */
    protected $promises;
    
    /**
     * Class constructor
     * 
     * @param DataEnricher $invoker
     * @param string       $property  Property key with the processing instruction
     */
    public function __construct(DataEnricher $invoker, $property)
    {
        $this->property = $property;
    }
    
    /**
     * Get the property key that holds the processing instruction
     * 
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }
    
    /**
     * Prepare processing to nodes
     * 
     * @param Node[] $nodes
     */
    public function prepare(array $nodes)
    {
        $this->promises = $this->request($nodes);
    }
    
    /**
     * Do async requests for each node
     * 
     * @param Node[] $nodes
     * @return \SplObjectStorage|Promise\PromiseInterface[]
     */
    protected function request($nodes)
    {
        $client = new Client();
        $promises = new \SplObjectStorage();
        
        foreach ($nodes as $node) {
            if ($node->hasInstruction($this)) {
                $url = $node->getInstruction($this);
                $promises[$node] = $client->getAsync($url);
            }
        }
        
        return $promises;
    }        
    
    /**
     * Apply results to a node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        if (!isset($this->promises[$node])) {
            return;
        }
        
        $result = null;
        $response = $this->promises[$node]->wait();

        if ($this->hasExpectedResponse($response)) {
            $result = json_decode($response->body());

            if (!$result) {
                $url = $node->getInstruction($this);
                trigger_error("Failed to fetch '$url': Corrupt JSON response", E_USER_WARNING);
            }
        }

        $node->setResult($result);
    }
    
    /**
     * Check if we got an expected response
     * 
     * @param Response $response
     * @return boolean
     */
    protected function hasExpectedResponse(Response $response)
    {
        $status = $response->getStatusCode();
        $contentType = preg_replace('/\s*;.*$/', '', $response->getHeader('content-type'));
        
        if ($status >= 300 || !in_array($contentType, ['application/json', 'text/plain'])) {
            $url = $node->getInstruction($this);
            
            if ($contentType === 'text/plain') {
                $message = $response->getBody();
            } else {
                $message = "Server responded with a $status status and $contentType";
            }
            
            trigger_error("Failed to fetch '$url': $message", E_USER_WARNING);
            return false;
        }
        
        return true;
    }
}