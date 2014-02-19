<?php

namespace Queue\Consumer;

class Consumer extends \Queue\Base
{
	public $queue;
	
	public function getQueue()
	{
		try {
			$this -> channel = new \AMQPChannel($this -> connection);
			$this -> queue = new \AMQPQueue($this -> channel);
			$this -> queue -> setName('direct_messages');
			$this -> queue -> declareQueue();
			$this -> queue -> bind($this -> exchangeType, $this -> routingKey);

		} catch (\Exception $e) {
			echo 'Oooops: ' . $e -> getMessage();
		}
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