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
	
	
	public function listencreatorsAction()
	{	
		$this -> queueCreators = new Consumer();
		$this -> queueCreators -> connect(['host' => $this -> config -> queue -> host,
								   		   'port' => $this -> config -> queue -> port,
										   'login' => $this -> config -> queue -> login,
										   'password' => $this -> config -> queue -> password,
										   'exchangeName' => $this -> config -> queue -> harvesterCreators -> exchange,
		                                   'exchangeType' => $this -> config -> queue -> harvesterCreators -> type,
										   'routing_key' => $this -> config -> queue -> harvesterCreators -> routing_key
										  ]);
		$this -> queueCreators -> setExchange();		
		$this -> queueCreators -> getQueue();
		
		while (true) {
			$job = $this -> queueCreators -> getItem();

			if($job) {
                $this -> queueCreators -> ackItem($job);
                $t = new \Jobs\Grabber\Parser\FacebookPage($this -> getDi());
                $t -> run($job);
            } else {
            	//print_r("No items in queue\n\r");
            	sleep(2);
            }
       	}
	}
	
}
