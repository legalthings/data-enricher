<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Transform processor, apply transformation functions on data
 */
class Transform implements Processor
{
    use Processor\Implementation;
    
    /**
     * Allowed transformation functions
     * @var array 
     */
    public $functions;
    
    
    /**
     * Class constructor
     * 
     * @param string $property  Property key with the processing instruction
     */
    public function __construct($property)
    {
        $this->property = $property;
        $this->functions = $this->getDefaultFunctions();
    }
    
    /**
     * Default transformation functions
     * @var array
     */
    public function getDefaultFunctions()
    {
        $class = 'LegalThings\DataEnricher\Processor\Transform';

        return [
            'hash' => 'hash',
            'hash_hmac' => 'hash_hmac',
            'base64_encode' => 'base64_encode',
            'base64_decode' => 'base64_decode',
            'json_encode' => 'json_encode',
            'json_decode' => 'json_decode',
            'serialize' => 'serialize',
            'unserialize' => 'unserialize',
            'strtotime' => 'strtotime',
            'date' => 'date',
            'public_encrypt' => "$class::public_encrypt",
            'private_encrypt' => "$class::private_encrypt",
            'private_decrypt' => "$class::private_decrypt",
            'generate_private_key' => "$class::generate_private_key",
            'generate_public_key' => "$class::generate_public_key",
            'generate_signature' => "$class::generate_signature",
            'verify_signature' => "$class::verify_signature"
        ];
    }
    
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $instruction = $node->getInstruction($this);
        $transformations = !is_array($instruction) ? [$instruction] : $instruction;
        
        if (isset($node->input)) {
            $input = $this->resolveNodes($node->input);
        }
        
        foreach ($transformations as $transformation) {
            if (is_string($transformation) && !isset($input)) {
                continue;
            }
            
            if (is_string($transformation)) {
                list($key, $args) = explode(':', $transformation) + [null, null];
            } elseif (is_object($transformation) || is_array($transformation)) {
                $transformation = (object)$transformation;
                $key = isset($transformation->function) ? $transformation->function : null;
                $args = isset($transformation->args) ? $transformation->args : [];
            }
            
            if (!isset($this->functions[$key])) {
                throw new \Exception("Unknown transformation '$key'");
            }
            
            if (isset($args)) {
                $args = $this->resolveNodes($args);
            }
            
            $fn = $this->functions[$key];
            
            if (is_string($transformation)) {
                $result = isset($args) ? call_user_func($fn, $args, $input) : call_user_func($fn, $input);
            } elseif (is_object($transformation)) {
                $result = call_user_func_array($fn, $args);
            }
        }
        
        // super crutch to solve mongo and openssl global state issue
        $this->getOpenSslErrors();
        
        if (isset($result)) {
            $node->setResult($result);
        }
    }
    
    /**
     * Resolve instructions of nodes by getting their results
     * 
     * @param Node $node
     * 
     * @return mixed $result
     */
    protected function resolveNodes($node) {
        if (!$node instanceof Node) {
            return $node;
        }

        return $node->getResult();
    }
    
    
    /**
     * Generate a private key
     * 
     * @param array $options
     * @throws RuntimeException
     * @return string
     */
    public function generate_private_key($options = [])
    {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        
        $input = $options + $config;
        $res = openssl_pkey_new($input);
        
        openssl_pkey_export($res, $key); 
        return $key;
    }
    
    /**
     * Generate a public based on existing private key
     * 
     * @param string $privateKey
     * @param string $passphrase
     * @throws \RuntimeException
     * @return string
     */
    public function generate_public_key($privateKey, $passphrase = null)
    {
        $privKey = openssl_get_privatekey($privateKey, $passphrase);
        
        if (!is_resource($privKey)) {
            $type = gettype($privKey);
            throw new \Exception("Expected private key to be resource, got: '$type'. Check if the given private key is in correct syntax.");
        }
        
        $pubKey = openssl_pkey_get_details($privKey);
        $key = $pubKey["key"];
        
        return $key;
    }
    
    /**
     * Encrypts data with private key
     * 
     * @link http://php.net/manual/en/function.openssl-private-encrypt.php
     * 
     * @param string $data
     * @param string $key          The private key
     * @param string $passphrase
     * @param int $padding
     * 
     * @return string
     */
    function private_encrypt($data, $key, $passphrase = null, $padding = OPENSSL_PKCS1_PADDING)
    {
        $privKey = openssl_get_privatekey($key, $passphrase);
        
        if (!is_resource($privKey)) {
            $type = gettype($privKey);
            throw new \Exception("Expected private key to be resource, got: '$type'. Check if the given private key is in correct syntax.");
        }
        
        openssl_private_encrypt($data, $result, $privKey, $padding);
        
        if (!is_string($result)) {
            $errors = join(', ', $this->getOpenSslErrors());
            throw new \Exception("Failed to encrypt data. Result: '$result'. Errors: '$errors'");
        }
        
        $base64 = base64_encode($result);
        
        return $base64;
    }
    
    /**
     * Decrypts data with public key, that was encrypted with private key
     * 
     * @link http://php.net/manual/en/function.openssl-private-decrypt.php
     * 
     * @param string $data
     * @param string $key          The public key
     * @param string $passphrase
     * @param int $padding
     * 
     * @return string
     */
    function private_decrypt($data, $key, $padding = OPENSSL_PKCS1_PADDING)
    {
        if ($this->is_base64($data)) {
            $data = base64_decode($data);
        }
        
        openssl_public_decrypt($data, $result, $key, $padding);
        
        if (!is_string($result)) {
            throw new \Exception("Failed to decrypt data. Result: '$result'");
        }
        
        return $result;
    }
    
    /**
     * Encrypts data with public key
     * 
     * @link http://php.net/manual/en/function.openssl-public-encrypt.php
     * 
     * @param string $data
     * @param string $key    The public key
     * @param int $padding
     * 
     * @return string
     */
    function public_encrypt($data, $key, $padding = OPENSSL_PKCS1_PADDING)
    {
        openssl_public_encrypt($data, $result, $key, $padding);
        
        if (!is_string($result)) {
            $errors = join(', ', $this->getOpenSslErrors());
            throw new \Exception("Failed to encrypt data. Result: '$result'. Errors: '$errors'");
        }
        
        $base64 = base64_encode($result);
        
        return $base64;
    }
    
    /**
     * Generate signature based on private key
     * 
     * @link http://php.net/manual/en/function.openssl-sign.php
     * 
     * @param string $data
     * @param string $key         The private key
     * @param string $passphrase
     * @param int $signature_alg
     * 
     * @return string
     */
    function generate_signature($data, $key, $passphrase = null, $signature_alg = OPENSSL_ALGO_SHA1)
    {
        $privKey = openssl_get_privatekey($key, $passphrase);
        
        if (!is_resource($privKey)) {
            $type = gettype($privKey);
            throw new \Exception("Expected private key to be resource, got: '$type'. Check if the given private key is in correct syntax.");
        }
        
        openssl_sign($data, $result, $privKey, $signature_alg);
        
        if (!is_string($result)) {
            throw new \Exception("Failed to encrypt data. Result: '$result'");
        }
        
        $base64 = base64_encode($result);
        
        return $base64;
    }
    
    /**
     * Verify signature that was encrypted with private key through public key
     * 
     * @link http://php.net/manual/en/function.openssl-verify.php
     * 
     * @param string $data
     * @param string $signature
     * @param string $key         The public key
     * @param string $passphrase
     * @param int $signature_alg
     * 
     * @return string
     */
    function verify_signature($data, $signature, $key, $signature_alg = OPENSSL_ALGO_SHA1)
    {
        if ($this->is_base64($signature)) {
            $signature = base64_decode($signature);
        }
        
        return openssl_verify($data, $signature, $key, $signature_alg);
    }
    
    /**
     * Check if input is valid base64
     * 
     * @param string $data
     * 
     * @return boolean
     */
    function is_base64($data)
    {
        return base64_encode(base64_decode($data)) === $data;
    }
    
    function getOpenSslErrors()
    {
        $errors = [];
        
        // super crutch to solve mongo and openssl global state issue
        for ($i = 0; $i <= 255; $i++) {
            $error = openssl_error_string();
            
            if (!$error) {
                break;
            }
            
            $errors[] = $error;
        }
        
        return $errors;
    }
}
