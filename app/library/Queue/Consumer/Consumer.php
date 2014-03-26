<?php

namespace Queue\Consumer;

class Consumer extends \Queue\Base
{
	public $queue;
	
	public function getQueue()
	{
		try {
			$this -> queue = new \AMQPQueue($this -> channel);
			$this -> queue -> setName($this -> routingKey);
			$this -> queue -> setFlags(AMQP_DURABLE);
			$this -> queue -> declareQueue();			
			$this -> queue -> bind($this -> exchangeName, $this -> routingKey);			

		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
	}

/*	public function getQueue()
	{
		try {
			$this -> channel = new \AMQPChannel($this -> connection);
			$this -> queue = new \AMQPQueue($this -> channel);
			$this -> queue -> setName($this -> exchangeName);
			$this -> queue -> declareQueue();
			$this -> queue -> bind($this -> exchangeName, $this -> routingKey);

		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
	}*/

	public function consumeItem($object, $callback)
	{
		return $this -> queue -> consume($object, $callback);
	}

	public function getItem()
	{
		return $this -> queue -> get();
	}

	public function ackItem($item)
	{
		$this -> queue -> ack($item -> getDeliveryTag());
	}

}