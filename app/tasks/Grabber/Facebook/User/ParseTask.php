<?php

namespace Tasks\Facebook\User;

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
								   'exchangeName' => $this -> config -> queue -> harvester -> exchange,
                                   'exchangeType' => $this -> config -> queue -> harvester -> type,
								   'routing_key' => $this -> config -> queue -> harvester -> routing_key
								  ]);
		$this -> queue -> setExchange();	
		$this -> queue -> getQueue();
		
		while (true) {
			$job = $this -> queue -> getItem();

            if($job) {
                $this -> queue -> ackItem($job);
                $t = new \Jobs\Grabber\Parser\Facebook($this -> getDi());
                $t -> run($job);
            } else {
            	print_r("No items in queue\n\r");
            	sleep(2);
            }
       	} 
	}
}