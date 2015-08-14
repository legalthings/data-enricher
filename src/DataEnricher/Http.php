<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher as DataEnricher;
use Jasny\DotKey;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

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
     * Sanatize by removing these properties.
     * @var array
     */
    public $sanatizeProperties;
    
    
    /**
     * Class constructor
     * 
     * @param DataEnricher $invoker
     * @param string       $property  Property key which should trigger the processor
     */
    public function __construct(DataEnricher $invoker, $property)
    {
        $this->property = $property;
        $this->sanatizeProperties = array_keys($invoker->processors);
    }
    
    /**
     * Enrich target
     * 
     * @param array|object $target
     * @return array|object
     */
    public function applyTo(&$target)
    {
        $map = $this->mapUrls($target);
        $this->process($target, $map);
        
        return $target;
    }
    
    /**
     * Create map of property paths and urls
     * 
     * @param array|object $target
     * @param string       $path
     * @param array        $map    Map or urls
     * @return array
     */
    protected function mapUrls(&$target, $path = null, &$map = [])
    {
        $prop = $this->property;
        
        foreach ($target as $key => &$value) {
            if (!is_object($value) && !is_array($value)) continue;
            
            $curPath = ($path ? $path + '.' : '') + $key;
            
            if (is_object($value) && isset($value->$prop)) {
                $map[$curPath] = $value->$prop;
                $value = null;
            }
            
            $this->mapUrls($value, $curPath, $map);
        }
        
        return $map;
    }
    
    /**
     * Fetch the data and insert it the 
     * 
     * @param array|object $target
     * @param array        $map     Map or urls
     */
    protected function process(&$target, $map)
    {
        $client = new Client();
        $promises = [];
        
        foreach ($map as $path => $url) {
            $promises[$path] = $client->getAsync($url);
        }
        
        $responses = Promise\unwrap($promises);
        
        foreach ($responses as $path => $response) {
            $url = $map[$path];
            $status = $response->getStatusCode();
            $contentType = preg_replace('/\s*;.*$/', '', $response->getHeader('content-type'));
            
            // Unexpected response
            if ($status >= 300 || !in_array($contentType, ['application/json', 'text/plain'])) {
                $message = $contentType === 'text/plain'
                    ? $response->getBody()
                    : "Server responded with a $status status and $contentType";
                trigger_error("Failed to fetch '$url': $message", E_USER_WARNING);
            }

            // OK
            $data = json_decode($response->body());
            if (!$data) trigger_error("Failed to fetch '$url': Corrupt JSON response", E_USER_WARNING);
            
            $this->sanatize($data, $url);
            
            $target = DotKey::on($target)->set($path, $data);
        }
    }
    
    /**
     * Remove all properties starting with an underscore
     * 
     * @param object|array $data
     */
    protected function sanatize(&$data, $url, $path = null)
    {
        if (empty($this->sanatizeProperties)) return;
        
        foreach ($data as $key => &$value) {
            $curPath = ($path ? $path + '.' : '') + $key;
            
            if (is_object($data) && in_array($key, $this->sanatizeProperties)) {
                unset($data[$key]);
                trigger_error("Sanatized response of $url: Removed $path", E_USER_NOTICE);
                continue;
            }
            
            if (is_object($data) || is_array($data)) $this->sanatize($value, $url, $curPath);
        }
    }
}
