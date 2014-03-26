<?php

namespace Queue;

class Base
{
	public $connection 		= false;
	public $exchange 		= false;
	public $exchangeType;
	public $exchangeName;
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
			$this -> channel = new \AMQPChannel($this -> connection);

			$this -> exchangeType = $config['exchangeType'];
			$this -> exchangeName = $config['exchangeName'];
			$this -> routingKey = $config['routing_key'];
						
		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
		
		return $this;
	}

	public function setExchange()
	{
		try {
			$this -> exchange = new \AMQPExchange($this -> channel);
			$this -> exchange -> setName($this -> exchangeName);
			$this -> exchange -> setType($this -> exchangeType);

		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
	}

	public function closeConnection()
	{
	}
}