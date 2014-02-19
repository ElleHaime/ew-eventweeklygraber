<?php

namespace Queue\Producer;

class Producer extends \Queue\Base
{
	public function setExchange()
	{
		try {
			$this -> channel = new \AMQPChannel($this -> connection);
			$this -> exchange = new \AMQPExchange($this -> channel);
			$this -> exchange -> setName($this -> exchangeType);

			$this -> channel = new \AMQPChannel($this -> connection);
			$this -> exchange = new \AMQPExchange($this -> channel);
			$this -> exchange -> setName($this -> exchangeType);
			
		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
	}

	public function publish($message)
	{
		$this -> exchange -> publish($message, $this -> routingKey);
	}

}