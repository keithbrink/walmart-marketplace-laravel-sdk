<?php 

namespace KeithBrink\Walmart;

use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;
use Exception;
use Cache;

/**
 * When making any calls to the sendRequest function, you must have previously 
 * set all the parameters for that request. These are the functions that you 
 * must call before the sendRequest call:
 * 
 * setEndpoint($endpoint) with the endpoint for the request
 * 
 * setMethod($method) with the method for the request (valid methods are GET, 
 *     POST, and DELETE)
 * 
 */

class WalmartCore {

    private $endpoint, $method, $data, $options, $response, $consumer_id, $private_key, $base_url;

    private $content_type = 'application/xml';

    /**
     * Constructor
     * Set the private key, consumer ID, and base URL from config.
     * Optionally set a different key, ID, and URL than 
     * the configured ones.
     * 
     */
    function __construct($private_key = null, $consumer_id = null, $base_url = null) {
        $this->consumer_id = $consumer_id ?: config('walmart.consumer_id');
        $this->private_key = $private_key ?: config('walmart.private_key');
        $this->base_url = $base_url ?: config('walmart.base_url');
        if(!$this->consumer_id || !$this->private_key || !$this->base_url) {
            throw new Exception('Login information not set properly.');
        }
    }

    /**
     * Generic send request function
     * Response is saved as a property ($response)
     *       
     * @return boolean True/False
     */
    protected function sendRequest() {
        $this->setHeaders();
        if($this->checkParameters()) {
            $url = $this->constructUrl();

            $client = new Client();
            try {
                $response = $client->request($this->method, $url, $this->options);
            } catch (ClientException $e) {
                $response = $this->handleError($e);
            } catch (ServerException $e) {
                $response = $this->handleError($e);
            }

            if($response->getStatusCode() == 401) {
                throw new \Exception('Unauthorized');
            }

            $this->response = $response;

            $this->resetParameters();

            return true;
        }
    }

    /**
     * Construct the request URL from the base URL, Client Code, and the endpoint
     *     
     * @return string The request URL
     */
    private function constructUrl() {
        return $this->base_url . '/' . $this->endpoint;
    }

    /**
     * Check if the current set parameters are invalid
     * If they are invalid, then throw an Exception
     * If they are valid, return true
     *     
     * @return mixed \Exception when invalid, bool true when valid
     */
    private function checkParameters() {
        foreach(['endpoint', 'method', 'content_type', 'options', 'base_url'] as $property) {
            if(!$this->$property) {
                throw new Exception('Property not set sending request: ' . $property);
            }
        }        
        return true;
    }

    /**
     * Reset the parameters so they are ready for the next request
     *     
     * @return mixed \Exception when invalid, bool true when valid
     */
    private function resetParameters() {
        foreach(['endpoint', 'method', 'options'] as $property) {
            $this->$property = null;
        }        
        return true;
    }

    /**
     * Get the response after a completed request
     * @return array The json-decoded body of the response
     */
    protected function getResponse() {
        if($this->response) {  
            $result = (string) $this->response->getBody();  
            $xml_check = strpos($result, '<?xml') !== false ? true : false;  
            if($xml_check) {
                $xml_namespace = strpos($result, '<ns3:') !== false ? 'ns3' : 'ns2';
                $result = simplexml_load_string($result, null, 0, $xml_namespace, true);
                $result = json_encode($result);
            } 
            $array = json_decode($result,TRUE);
            return $array;
        } else {
            throw new \Exception('No response available. Did you send the request using sendRequest()?');
        }
    }

    /**
     * Log Exceptions without throwing them, and return a false response
     * @param  \Exception $exception Any generic exception
     * @return bool                False
     */
    protected function handleError(\Exception $exception) {
        \Log::error($exception);
        if($exception instanceof ClientException) {
            if($exception->hasResponse()) {
                $response = $exception->getResponse();                
                return $response;
            }
        }
        return false;
    }

    /**
     * Add data to GET request, Guzzle will use 
     * http_build_query to add the parameters to the URL. 
     * 
     * @param array $data 
     */
    protected function setGetData(array $data) {
        $this->options['query'] = $data;
    }

    /**
     * Add XML data to POST request
     * 
     * @param string $data 
     */
    protected function setPostXmlData($data) {
        $this->options['multipart'] = array(array(
            'name' => 'file',
            'contents' => $data,
        ));
        $this->content_type = 'multipart/form-data';
    }

    /**
     * Set the endpoint (excluding the Dasco base URL and client code)
     * For example, 'account/$id_code/detail'
     *    
     * @param string $endpoint URL endpoint (the part after client code)
     */
    protected function setEndpoint(string $endpoint) {
        $this->endpoint = ltrim($endpoint);
    }

    /**
     * Set the method of the request
     * The only valid request methods are 'GET', 'POST', 'DELETE'
     * The method is not case sensitive
     *    
     * @param string $method Method ('GET', 'POST', or 'DELETE')
     */
    protected function setMethod(string $method) {
        if(!in_array(strtolower($method), ['get', 'post', 'delete'])) {
            throw new \Exception('Method type not supported by Walmart: ' . $method);
        }
        $this->method = ltrim($method);
    }

    protected function setHeaders() {
        $this->timestamp = round(microtime(true) * 1000);
        $this->options['headers'] = array(
            'WM_SVC.NAME' => 'Walmart Marketplace',
            'WM_QOS.CORRELATION_ID' => str_random(20),
            'WM_SEC.TIMESTAMP' => $this->timestamp,
            'WM_SEC.AUTH_SIGNATURE' => $this->getWalmartAuthSignature(),
            'WM_CONSUMER.ID' => $this->consumer_id,
            'WM_CONSUMER.CHANNEL.TYPE' => '9ebb75c2-1baf-4238-89f7-59a6c37bc347',
            'Content-Type' => $this->content_type,
            'Accept' => 'application/xml',
            'Host' => 'marketplace.walmartapis.com',
        );
    }

    /**
     * Get the authorization signature to send in the request
     * @return string         The base 64 encoded signatre
     */
    protected function getWalmartAuthSignature() {        
        $data = $this->consumer_id."\n";
        $data .= $this->getFullUrl()."\n";
        $data .= $this->method."\n";
        $data .= $this->timestamp."\n";

        $pem = $this->convertPkcs8ToPem(base64_decode($this->private_key));
        $openssl_key = openssl_pkey_get_private($pem);

        // SIGN THE DATA. USE sha256 HASH
        $hash = defined("OPENSSL_ALGO_SHA256") ? OPENSSL_ALGO_SHA256 : "sha256";
        if (openssl_sign($data, $signature, $openssl_key, $hash)) { 
            //ENCODE THE SIGNATURE AND RETURN
            return base64_encode($signature);
        }
    }

    protected function getFullUrl() {
        if($this->method == 'GET' && $this->options['query']) {
            $url = $this->base_url.$this->endpoint.'?'.http_build_query($this->options['query']);
        } else {
            $url = $this->base_url.$this->endpoint;
        }
        return $url;
    }

    /**
     * Convert the Pkcs8 key to a Pem key
     * @param  string $der The base64 decoded private key
     * @return string      The Pem key
     */
    protected function convertPkcs8ToPem($der) {
        static $BEGIN_MARKER = "-----BEGIN PRIVATE KEY-----";
        static $END_MARKER = "-----END PRIVATE KEY-----";
        $key = base64_encode($der);
        $pem = $BEGIN_MARKER . "\n";
        $pem .= chunk_split($key, 64, "\n");
        $pem .= $END_MARKER . "\n";
        return $pem;
    }
}