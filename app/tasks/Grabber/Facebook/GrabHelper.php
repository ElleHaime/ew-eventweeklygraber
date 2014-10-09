<?php

namespace Tasks\Facebook;

use \Vendor\Facebook\Extractor,
	\Queue\Producer\Producer,
	\Models\Cron;


trait GrabHelper
{
	public function initQueue($source)
	{
		$this -> fb = new Extractor($this -> getDi());

		$this -> queue = new Producer();
		$this -> queue -> connect(['host' => $this -> config -> queue -> host,
								   'port' => $this -> config -> queue -> port,
								   'login' => $this -> config -> queue -> login,
								   'password' => $this -> config -> queue -> password,
								   'exchangeName' => $this -> config -> queue -> $source -> exchange,
                                   'exchangeType' => $this -> config -> queue -> $source -> type,
								   'routing_key' => $this -> config -> queue -> $source -> routing_key
								  ]);
		$this -> queue -> setExchange();		
	}
	

    public function testAction(array $args)
    {
        $this -> queue = new Producer();
        $this -> queue -> connect(['host' => $this -> config -> queue -> host,
                                   'port' => $this -> config -> queue -> port,
                                   'login' => $this -> config -> queue -> login,
                                   'password' => $this -> config -> queue -> password,
                                   'exchangeName' => $this -> config -> queue -> harvester -> exchange,
                                   'exchangeType' => $this -> config -> queue -> harvester -> type,
                                   'routing_key' => $this -> config -> queue -> harvester -> routing_key
                                  ]);
        $this -> queue -> setExchange();
        print_r($this -> queue);
        for ($i = 0; $i < 1000; $i++) {
            echo ".";
            $this -> queue -> publish('Element #' . $i . ' published');
        }
        print_r("\n\rready");
    }
    

	protected function publishToBroker($event, $args, $resultType)
	{
       	$data = ['args' => $args,
       			 'item' => json_decode(json_encode($event), true),
        		 'type' => $resultType];
       	
        $this -> queue -> publish(serialize($data));
	}
    
    
	public function getState()
	{
		return $this -> state;
	}
	
	
	public function setState($state = 'idle')
	{
		$this -> state = $state;
		return $this; 
	}
}