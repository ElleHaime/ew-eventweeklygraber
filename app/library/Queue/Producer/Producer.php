<?php

namespace Queue\Producer;

class Producer extends \Queue\Base
{
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