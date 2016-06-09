<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * Load an external source
 */
class Http implements Processor
{
    use Processor\Implementation;
    
    /**
     * Get the URL from a node
     * 
     * @param Node $node
     * @return string
     */
    protected function getUrl($node)
    {
        $url = $node->getInstruction($this);
        
        $type = (is_object($url) ? get_class($url) . ' ' : '') . gettype($url);
        if (!assert(is_string($url), "Expected '{$this->property}' to be a string, but got a $type")) {
            return null;
        }
        
        return $url;
    }
    
    /**
     * Do async request for node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $url = $this->getUrl($node);
        if (!isset($url)) {
            return;
        }

        $client = new Client(['http_errors' => false]);
        $promise = $client->getAsync($url)->then(function (Response $response) use ($node) {
            $this->applyResult($node, $response);
        });
        
        $node->setResult($promise);
    }
    
    /**
     * Apply results to a node
     * 
     * @param Node     $node
     * @param Response $response
     */
    protected function applyResult(Node $node, Response $response)
    {
        $result = null;

        if ($this->hasExpectedResponse($node, $response)) {
            $result = json_decode($response->getBody());

            if (!$result) {
                $url = $this->getUrl($node);
                trigger_error("Failed to fetch '$url': Corrupt JSON response", E_USER_WARNING);
            }
        }

        $node->setResult($result);
    }
    
    /**
     * Check if we got an expected response
     * 
     * @param Node     $node
     * @param Response $response
     * @return boolean
     */
    protected function hasExpectedResponse(Node $node, Response $response)
    {
        $status = $response->getStatusCode();
        $contentType = preg_replace('/\s*;.*$/', '', $response->getHeaderLine('Content-Type'));
        
        if ($status >= 300 || !in_array($contentType, ['application/json', 'text/plain'])) {
            $url = $this->getUrl($node);
            
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
