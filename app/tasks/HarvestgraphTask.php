<?php

namespace Tasks;

use \Vendor\FacebookGraph\FacebookSession,
	\Vendor\FacebookGraph\FacebookRequest,
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
								   'exchangeName' => $this -> config -> queue -> harvesterCreators -> exchange,
                                   'exchangeType' => $this -> config -> queue -> harvesterCreators -> type,
								   'routing_key' => $this -> config -> queue -> harvesterCreators -> routing_key
								  ]);
		$this -> queue -> setExchange();
		
		$model = new Event();
		$creators = $model -> getCreators();
		if (!empty($creators)) {
						
		}
		
	}
}