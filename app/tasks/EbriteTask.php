<?php

namespace Tasks;

use \Vendor\Eventbrite\Eventbrite,
	\Queue\Producer\Producer,
	\Models\Cron;


class ebriteTask extends \Phalcon\CLI\Task
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
		$this -> ebrite -> getCategories();
		$this -> ebrite -> getEventsByCity('Dublin');
	}
}