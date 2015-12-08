<?php

namespace Tasks\Eventbrite;

use \Vendor\Facebook\Extractor,
	\Queue\Consumer\Consumer;


class ParseTask extends \Phalcon\CLI\Task
{
	protected $queue;
	
	public function listenAction()
	{	
		$this -> queue = new Consumer();
		$this -> queue -> connect(['host' => $this -> config -> queue -> host,
									     'port' => $this -> config -> queue -> port,
									     'login' => $this -> config -> queue -> login,
									     'password' => $this -> config -> queue -> password,
									     'exchangeName' => $this -> config -> queue -> harvesterEbrite -> exchange,
	                                     'exchangeType' => $this -> config -> queue -> harvesterEbrite -> type,
									     'routing_key' => $this -> config -> queue -> harvesterEbrite -> routing_key
									   ]);
		$this -> queue -> setExchange();		
		$this -> queue -> getQueue();

		while (true) {
			$job = $this -> queue -> getItem();

			if($job) {
                $this -> queue -> ackItem($job);
                $t = new \Jobs\Grabber\Parser\Eventbrite($this -> getDi());
                $t -> run($job);
            } else {
            	print_r("\n\r" . date('H:i:s d-m-Y') . " :: no items in queue\n\r");
            	die();
            }
       	} 
	}
}