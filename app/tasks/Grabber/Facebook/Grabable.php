<?php

namespace Tasks\Facebook;

use \Vendor\Facebook\Extractor,
	\Vendor\FacebookGraph\FacebookSession,
	\Vendor\FacebookGraph\FacebookRequest,
	\Vendor\FacebookGraph\FacebookRequestException,
	\Queue\Producer\Producer,
	\Models\Cron;


trait Grabable
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
	
	public function initGraph()
	{
		try {
			FacebookSession::setDefaultApplication($this -> config -> facebook -> appId, 
												   $this -> config -> facebook -> appSecret);
			FacebookSession::enableAppSecretProof();
			$this -> fbSession = FacebookSession::newAppSession();
		} catch(\Exception $e) {
			print_r($e);
		}
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
	
	
	public function updateTask($arg, $state, $hash = false)
	{
		$task = Cron::findFirst($arg);
		$task -> state = $state;
		if ($hash) {
			$task -> hash = $hash;	
		} else {
			$task -> hash = time();
		}
		$task -> update();		
	}
	
	
	public function closeTask($arg)
	{
		$task = Cron::findFirst($arg);
		$task -> state = Cron::STATE_EXECUTED;
		$task -> update();		
	}
}