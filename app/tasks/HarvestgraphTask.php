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
	}
	
	public function harvestAction(array $args)
	{
		$this -> init();
		
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
		
		$model = new Event();
		$creators = $model -> getCreators();

		if (!empty($creators)) {
			foreach ($creators as $val) {
				// get page info
				$query = '/' . $val;
				try {
					$request = new FacebookRequest($this -> fbSession, 'GET', $query);
					$data = $request -> execute() -> getGraphObject() -> asArray();

					if (!empty($data)) {
						$this -> savePage($data);
						 
					} 
				} catch (FacebookRequestException $ex) {
					print_r($ex -> getMessage());
				}	
				
				
				// save events					
				$query = '/' . $val . '/events?fields=id,start_time,end_time,name,location,venue,description';
				try {
					$request = new FacebookRequest($this -> fbSession, 'GET', $query);
					$data = $request -> execute() -> getGraphObject() -> asArray();

					if (!empty($data['data'])) {
						foreach ($data['data'] as $event) {
							$this -> publishToEventBroker($event, $args, 'creators');
						}
					} 
				} catch (FacebookRequestException $ex) {
					print_r("oooooooooops\n\r");					
				}	
			}								
		}
		
		print_r("done\n\r");
		die();
	}
	
	
	protected function publishToEventBroker($event, $args, $resultType)
	{
       	$data = ['args' => $args,
       			 'item' => json_decode(json_encode($event), true),
        		 'type' => $resultType];
       	
        $this -> queue -> publish(serialize($data));
	}

	
	protected function publishToPageBroker($page, $args)
	{
       	$data = ['item' => json_decode(json_encode($event), true)];
        $this -> queue -> publish(serialize($data));
	}
	
	protected function savePage($data)
	{
		$newPage = [];
		$newPage['fb_uid'] = $data['id'];
		$newPage['fb_uname'] = $data['username'];
		$newPage['name'] = $data['name'];
		$newPage['link'] = $data['link'];
		$newPage['category'] = $data['category'];
		if (isset($data['phone'])) {
			$newPage['phone'] = $data['phone'];
		}
		if (isset($data['site'])) {
			$newPage['site'] = $data['website'];
		}
		if (isset($data['likes'])) {
			$newPage['likes'] = $data['likes'];
		}
		$description = '';
		if (isset($data['about'])) {
			$description = $data['about'];	
		} elseif (isset($data['description'])) {
			$description = $data['description'];
		} elseif (isset($data['company_overview'])) {
			$description = $data['company_overview'];
		}
		$newPage['description'] = $description;
					
		$locations = new \Models\Location();
       	$locExists = $locations -> createOnChange(['latitude' => $data['location'] -> latitude, 
       											   'longitude' => $data['location'] -> longitude]);
        if ($locExists) {
			$newPage['location_id'] = $locExists -> id;
		}
					
		$page = new \Models\Page();
		$page -> assign($newPage);
		if ($page -> save()) {
			return $page -> id;
		} else {
			return false;
		}
	}
}