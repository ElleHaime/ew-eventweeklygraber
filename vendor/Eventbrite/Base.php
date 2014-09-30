<?php

namespace Vendor\Eventbrite;

use \Exception as Exception;


if (!function_exists('curl_init')) {
  throw new Exception('Eventbrite needs the CURL PHP extension.');
}

if (!function_exists('json_decode')) {
  throw new Exception('Eventbrite needs the JSON PHP extension.');
}


abstract class Base
{
	const TOKEN_TYPE_PERSONAL	= 1;
	const TOKEN_TYPE_CLIENT		= 2;

	protected $apiVersion	= 'v3';
	protected $requestUrl	= 'https://www.eventbriteapi.com';
	
	protected $appKey 		= null;
	protected $authToken	= null;
	protected $anonToken	= null;
	protected $clientSecret = null;
	protected $tokenType	= '';
	
	protected $curlMethod 	= 'GET';
	protected $curlEntity	= null;
	protected $curlEntityId	= null;
	protected $curlUrl		= null;
	protected $curlArgs 	= [];
	protected $curlPage		= 1;
	
	protected $curlOpts = [
		CURLOPT_RETURNTRANSFER => true,
	];
	
	
	public function __construct($config)
	{
		$this -> setAppKey($config -> appKey)
			  -> setAuthToken($config -> authToken)
			  -> setAnonToken($config -> anonToken)
			  -> setClientSecret($config -> clientSecret);
	}
	
	protected function auth()
	{
	}
	
	protected function makeRequest()
	{
		$ch = curl_init();
		foreach ($this -> curlOpts as $option => $val) {
           	curl_setopt($ch, $option, $val);
        }

        if ($this -> tokenType == self::TOKEN_TYPE_PERSONAL) {
			$this -> composePersonalUrl();
	        curl_setopt($ch, CURLOPT_URL, $this -> curlUrl);
        }
        
        $jsonData = curl_exec($ch);
		$respInfo = curl_getinfo($ch);
		$respData = get_object_vars(json_decode($jsonData));
        curl_close($ch);
        
        return $respData;
	}	
	
	
	public function setAppKey($arg)
	{
		$this -> appKey = $arg;
		return $this;
	}
	
	public function getAppKey()
	{
		return $this -> appKey;
	}
	
	public function setAuthToken($arg)
	{
		$this -> authToken = $arg;
		return $this;
	}
	
	public function getAuthToken()
	{
		return $this -> authToken;
	}
	
	public function setAnonToken($arg)
	{
		$this -> anonToken = $arg;
		return $this;
	}
	
	public function getAnonToken()
	{
		return $this -> anonToken;
	}
	
	public function setClientSecret($arg)
	{
		$this -> clientSecret = $arg;
		return $this;	
	}
	
	public function getClientSecret()
	{
		return $this -> clientSecret;
	} 
	
	public function setTokenType($arg)
	{
		$this -> tokenType = $arg;
		return $this;
	} 
	
	public function setFilter($arg, $val)
	{
		$this -> curlArgs[$arg] = $val;
		return $this;
	}
	
	public function setEntity($arg)
	{
		$this -> curlEntity = $arg;
		return $this;
	}
	
	public function setEntityId($arg)
	{
		$this -> curlEntityId = $arg;
		return $this;
	}
	
	protected function composePersonalUrl()
	{
		if (is_null($this -> curlEntity)) {
			throw new Exception('Oooops, you forgot to say me, what you wanna get. With love, you EventbriteAPI');
			return false;
		} 
		if (is_null($this -> authToken)) {
			throw new Exception('Oooops, you forgot about auth token, dude. With love, you EventbriteAPI');
			return false;
		}
		
		$this -> curlUrl = $this -> requestUrl . '/' . 
			   			   $this -> apiVersion . '/' .
			   			   $this -> curlEntity . '/?token=' .
			   			   $this -> authToken;
		if (!empty($this -> curlArgs)) {
			foreach ($this -> curlArgs as $arg => $val) {
				$this -> curlUrl .= '&' . $arg . '=' . $val;
			}
		}
	}
}