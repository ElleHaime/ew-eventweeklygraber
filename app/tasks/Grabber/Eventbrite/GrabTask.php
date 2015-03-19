<?php

namespace Tasks\Eventbrite;

use \Vendor\Eventbrite\Eventbrite,
	\Models\Grabber as Grabber,
	\Queue\Producer\Producer,
	\Models\Cron;


class GrabTask extends \Phalcon\CLI\Task
{
	protected $ebrite;
	protected $queue;
	
	public function init()
	{
		$this -> ebrite = new Eventbrite($this -> getDi());
		
		$this -> queue = new Producer();
		$this -> queue -> connect(['host' => $this -> config -> queue -> host,
								   'port' => $this -> config -> queue -> port,
								   'login' => $this -> config -> queue -> login,
								   'password' => $this -> config -> queue -> password,
								   'exchangeName' => $this -> config -> queue -> harvesterEbrite -> exchange,
                                   'exchangeType' => $this -> config -> queue -> harvesterEbrite -> type,
								   'routing_key' => $this -> config -> queue -> harvesterEbrite -> routing_key
								  ]);
		$this -> queue -> setExchange();
	}
	
	
	public function harvestAction()
	{
		$this -> init();

		$existed = Grabber::find(['grabber = "eventbrite"']);
		if ($existed) {
			 foreach ($existed as $item) {
print_r($item -> value . "\n\r");
				$events = $this -> ebrite -> getEventsByCity($item -> value, 
															 $item -> last_id);
				if ($events) {
					$lastId = $item -> last_id;
					foreach ($events as $ev) {
						$this -> publishToBroker($ev);
						$lastId = $ev -> id;
					}
					$item -> last_id = $lastId;
					$item -> update();
				}				
			 }
		}
print_r("done\n\r");
	}
	
	
	protected function publishToBroker($event)
	{
       	$data = ['item' => json_decode(json_encode($event), true)];
        $this -> queue -> publish(serialize($data));
	}
}