<?php

namespace Tasks;

use \Vendor\FacebookGraph\FacebookSession,
	\Vendor\FacebookGraph\FacebookRequest,
	\Vendor\FacebookGraph\FacebookRequestException,
	\Queue\Producer\Producer,
	\Models\Cron,
	\Models\Event;


class harvestgraphTask extends \Phalcon\CLI\Task
{
	protected $fbSession;
	
	
	public function init()
	{
		$appCfg = $this -> getDi() -> get('config');
		try {
			FacebookSession::setDefaultApplication($appCfg -> facebook -> appId, 
												   $appCfg -> facebook -> appSecret);
			FacebookSession::enableAppSecretProof();
			$this -> fbSession = FacebookSession::newAppSession();
		} catch(\Exception $e) {
			print_r($e);
		}
		
		$this -> queueEvent = new Producer();
		$this -> queueEvent -> connect(['host' => $this -> config -> queue -> host,
								   		'port' => $this -> config -> queue -> port,
								   		'login' => $this -> config -> queue -> login,
								   		'password' => $this -> config -> queue -> password,
								   		'exchangeName' => $this -> config -> queue -> harvester -> exchange,
                                   		'exchangeType' => $this -> config -> queue -> harvester -> type,
								   		'routing_key' => $this -> config -> queue -> harvester -> routing_key
								  	]);
		$this -> queueEvent -> setExchange();
		
		$this -> queueCreators = new Producer();
		$this -> queueCreators ->  connect(['host' => $this -> config -> queue -> host,
								   		    'port' => $this -> config -> queue -> port,
									   		'login' => $this -> config -> queue -> login,
									   		'password' => $this -> config -> queue -> password,
									   		'exchangeName' => $this -> config -> queue -> harvesterCreators -> exchange,
	                                   		'exchangeType' => $this -> config -> queue -> harvesterCreators -> type,
									   		'routing_key' => $this -> config -> queue -> harvesterCreators -> routing_key
									  	]);
		$this -> queueCreators -> setExchange();
	}
	
	public function harvestAction(array $args)
	{
		$this -> init();
		
		$model = new Event();
		$creators = $model -> getCreators();

		if (!empty($creators)) {
			foreach ($creators as $val) {
				// get page info
				$query = '/' . $val;
//print_r($query . "\n\r");				
				try {
					$request = new FacebookRequest($this -> fbSession, 'GET', $query);
					$data = $request -> execute() -> getGraphObject() -> asArray();
//print_r($data);
//print_r("\n\r");
					if (!empty($data)) {
						$this -> publishToPageBroker($data);
					} 
				} catch (FacebookRequestException $ex) {
					print_r($ex -> getMessage() . "\n\r");
				}	
				
				
				// save events					
				$query = '/' . $val . '/events?fields=id,owner,start_time,end_time,name,location,cover,venue,description,ticket_uri';
//print_r($query . "\n\r");				
				try {
					$request = new FacebookRequest($this -> fbSession, 'GET', $query);
					$data = $request -> execute() -> getGraphObject() -> asArray();

					if (!empty($data['data'])) {
						foreach ($data['data'] as $event) {
							$event -> creator = $val;
							$event -> pic_cover = $event -> cover;
							$this -> publishToEventBroker($event, $args, 'creators');
						}
					} 
				} catch (FacebookRequestException $ex) {
					print_r($ex -> getMessage() . "\n\r");					
				}	
			}								
		}
		
		$task = Cron::findFirst($args[3]);
        $task -> state = Cron::STATE_EXECUTED;
        $task -> update();
//print_r("done\n\r");
	}
	
	
	protected function publishToEventBroker($event, $args, $resultType)
	{
       	$data = ['args' => $args,
       			 'item' => json_decode(json_encode($event), true),
        		 'type' => $resultType];
       	
        $this -> queueEvent -> publish(serialize($data));
	}

	
	protected function publishToPageBroker($page)
	{
       	$data = ['item' => $page];
        $this -> queueCreators -> publish(serialize($page));
	}
}