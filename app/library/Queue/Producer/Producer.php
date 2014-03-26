<?php

namespace Queue\Producer;

class Producer extends \Queue\Base
{
	/*public function setExchange()
	{
		try {
			$this -> exchange = new \AMQPExchange($this -> channel);
			$this -> exchange -> setName($this -> exchangeName);
			$this -> exchange -> setType($this -> exchangeType);
			$this -> exchange -> declare();
	
		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
	} */

	public function publish($message)
	{
		try {
			$this -> exchange -> publish($message, $this -> routingKey);
		} catch (\Phalcon\Exception $e) {
			echo $e -> getMessage();
			exit(255);
		}
	}

}