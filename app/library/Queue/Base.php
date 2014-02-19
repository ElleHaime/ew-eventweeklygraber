<?php

namespace Queue;

class Base
{
	public $connection 		= false;
	public $exchange 		= false;
	public $exchangeType;
	public $routingKey;
	public $channel;

	
	public function connect($config)
	{
		try {
			$this -> connection = new \AMQPConnection(array('host' => $config['host'], 
										 	  'port' => $config['port'], 
										 	  'login' => $config['login'],
											  'password' => $config['password'])); 
			$this -> connection -> connect();
			if (!$this -> connection -> isConnected()) {
				die('Not connected :(' . PHP_EOL);
			} 

			$this -> exchangeType = $config['exchange'];
			$this -> routingKey = $config['routing_key'];
						
		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
		
		return $this;
	}

	public function closeConnection()
	{
	}
}