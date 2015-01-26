<?php

namespace Tasks;

use \Vendor\Facebook\Extractor,
	\Queue\Consumer\Consumer;


class listenerTask extends \Phalcon\CLI\Task
{
	protected $queueEvent;
	protected $queueCreators;


	public function listenAction()
	{	
		$this -> queueEvent = new Consumer();
		$this -> queueEvent -> connect(['host' => $this -> config -> queue -> host,
									   'port' => $this -> config -> queue -> port,
									   'login' => $this -> config -> queue -> login,
									   'password' => $this -> config -> queue -> password,
									   'exchangeName' => $this -> config -> queue -> harvester -> exchange,
	                                   'exchangeType' => $this -> config -> queue -> harvester -> type,
									   'routing_key' => $this -> config -> queue -> harvester -> routing_key
									  ]);
		$this -> queueEvent -> setExchange();		
		$this -> queueEvent -> getQueue();
		
		while (true) {
			$job = $this -> queueEvent -> getItem();

            if($job) {
                $this -> queueEvent -> ackItem($job);
                $t = new \Jobs\Grabber\Parser\Facebook($this -> getDi());
                $t -> run($job);
            } else {
            	//print_r("No items in queue\n\r");
            	sleep(2);
            }
       	}
	}
}
