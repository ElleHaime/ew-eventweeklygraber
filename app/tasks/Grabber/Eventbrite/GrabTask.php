<?php

namespace Tasks\Eventbrite;

use \Vendor\Eventbrite\Eventbrite,
	\Models\Grabber as Grabber,
	\Queue\Producer\Producer,
	\Models\Cron;


class GrabTask extends \Phalcon\CLI\Task
{
	const MAX_RATE_LIMIT	= 5000;
	
	const GRAB_TYPE_CITY 	= 'city';
	const GRAB_TYPE_COUNTRY	= 'country';
	
	protected $ebrite;
	protected $queue;
	protected $resultType;

	
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
	
	
	public function harvestAction(array $arg = array())
	{
		$this -> init();
		if (!empty($arg) && isset($arg['resultType'])) {
			$this -> resultType = $arg['resultType'];
		} else {
			$this -> resultType = self::GRAB_TYPE_CITY;
		}

		//$existed = Grabber::find(['grabber = "eventbrite" and type = "' . $this -> resultType . '"']);
		$existed = Grabber::find(['grabber = "eventbrite"']);
		if ($existed) {
			$requests = 0;
			
			foreach ($existed as $item) {
print_r("\n\r" . $item -> value);

				$this -> resultType == self::GRAB_TYPE_CITY ? $value = $item -> value : $value = 'IE';
				$events = $this -> ebrite -> getEventsByLocation($item -> value, 
															 	 $item -> last_id, 
																 $this -> resultType);

				if ($events) {
					$lastId = $item -> last_id;
					foreach ($events as $ev) {
						$item -> last_id = $ev -> id;
						$item -> update();
						
						if ($this -> resultType == self::GRAB_TYPE_CITY) {		
							$ev -> location_id = $item -> param;
						}
						$this -> publishToBroker($ev);
						$lastId = $ev -> id;
					}
				}					
			}
		}
print_r("\n\r" . date('H:i Y-m-d') . " :: harvesrt Eventbrite by " . $this -> resultType . " done\n\r");
die();
	}

	
	public function harvestIncorrectAction()
	{
		$this -> init();
		
		$args = ['eb_uid is not null', 'start_date is null', 'end_date is null'];
		$existed = (new \Models\Event()) -> getAllByParams($args);

		if (!empty($existed)) {
			foreach ($existed as $index => $ebUid) {
				$event = $this -> ebrite -> getEventById($ebUid);
				if ($event) {
					$this -> publishToBroker($event);
				}
			}
		}
		
		print_r("done\n\r");
		die();
	}

	
	protected function publishToBroker($event)
	{
       	$data = ['item' => json_decode(json_encode($event), true)];
        $this -> queue -> publish(serialize($data));
	}
}