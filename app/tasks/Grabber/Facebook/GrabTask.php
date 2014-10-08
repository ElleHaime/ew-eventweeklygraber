<?php

namespace Tasks\Facebook;

use \Vendor\Facebook\Extractor,
	\Queue\Producer\Producer,
	\Models\Cron;


class GrabTask extends \Phalcon\CLI\Task
{
	const IDLE = 'idle';
	const RUNNING = 'running';
	
	protected $state = self::IDLE;
	protected $fb;
	protected $queue;
	
	protected $userPagesUid 	= [];
	protected $userGoingUid 	= [];
	protected $pagesUid			= [];
	protected $friendsUid 		= [];
	protected $friendsGoingUid 	= [];
	
	protected $testCounter 		= 0;


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


	public function harvestAction(array $args)
	{
		error_reporting(E_ALL & ~E_NOTICE);		
		
		$this -> fb = new Extractor($this -> getDi());

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
        $queries = $this -> fb -> getQueriesScope();

        foreach ($queries as $key => $query) {

        	if ($query['name'] == 'user_event') {
        		$replacements = array($args[1]);
        		$fql = preg_replace($query['patterns'], $replacements, $query['query']);
        		$result = $this -> fb -> getCurlFQL($fql, $args[0]);

        		if (count($result -> event) > 0) {
        			foreach ($result -> event as $key => $ev) {
        				$this -> publishToBroker($ev, $args, $query['name']);
        			}
        		}
        	}

        	if ($query['name'] == 'user_page_uid') {
        		$this -> userPagesUid = $this -> processIds($query, $args, $args[1], 'page_admin', 'page_id');
        	}
        	 
        	if ($query['name'] == 'user_page_event' && !empty($this -> userPagesUid)) {
        		$this -> processEvents($query, $args, $this -> userPagesUid, 5);
        	}

        	if ($query['name'] == 'user_going_eid') {
        		$this -> userGoingUid = $this -> processIds($query, $args, $args[1], 'event_member', 'eid');
        	}
        	 
        	if ($query['name'] == 'user_going_event' && !empty($this -> userGoingUid)) {
        		$this -> processEvents($query, $args, $this -> userGoingUid);
        	}

        	if ($query['name'] == 'page_uid') {
        		$this -> pagesUid = $this -> processIds($query, $args, $args[1], 'page_fan', 'page_id');
        	}
        	 
        	if ($query['name'] == 'page_event' && !empty($this -> pagesUid)) {
        		$this -> processEvents($query, $args, $this -> pagesUid, 5);
        	}
        	 
        	if ($query['name'] == 'friend_uid') {
        		$this -> friendsUid = $this -> processIds($query, $args, $args[1], 'friend_info', 'uid2');
        	}
        	
        	if ($query['name'] == 'friend_event' && !empty($this -> friendsUid)) {
        		$this -> processEvents($query, $args, $this -> friendsUid);
        	}
        	
        	if ($query['name'] == 'friend_going_eid' && !empty($this -> friendsUid)) {
        		$this -> friendsGoingUid = $this -> processIds($query, $args, implode(',', $this -> friendsUid), 'event_member', 'eid');
        	}
        	
        	if ($query['name'] == 'friend_going_event' && !empty($this -> friendsGoingUid)) {
        		$this -> processEvents($query, $args, $this -> friendsGoingUid);
        	} 
        } 

        $task = Cron::findFirst($args[3]);
        $task -> state = Cron::STATE_EXECUTED;
        $task -> update(); 
        
        //print_r("\n\r\n\rSummary:" . $this -> testCounter);
	}
	
	protected function processIds($query, $args, $replacements, $table, $id)
	{
//print_r($query['name'] . "\n\r");		
		$resultScope = [];
		$replacements = array($replacements);
		$fql = preg_replace($query['patterns'], $replacements, $query['query']);
//print_r($fql . "\n\r");		
		$result = $this -> fb -> getCurlFQL($fql, $args[0]);

		if (count($result -> $table) > 0) {
			foreach ($result -> $table as $item) {
				$resultScope[] = json_decode(json_encode($item), true)[$id];
			}
		}	
//print_r($resultScope);
//print_r("\n\r");		
		return $resultScope; 
	}
	
	
	protected function processEvents($query, $args, $baseIds, $peace = 10)
	{
//print_r($query['name'] . "\n\r");
//print_r("Ids: " . count($baseIds) . "\n\r");	
//print_r("\n\r");
		$chunked = array_chunk($baseIds, $peace);
//print_r("Chunked: ");
//print_r($chunked);
//print_r("\n\r");
//print_r("Chunked count: " . count($chunked));
//print_r("\n\r");
		$currentChunk = 0;
		$start = true;
	
		do {
			$ids = implode(',', $chunked[$currentChunk]);
			if (count($query['patterns']) == 1) {
				$replacements = array($ids);
			} else {
				$replacements = array($ids, $args[1]);
			}
			$fql = preg_replace($query['patterns'], $replacements, $query['query']);
//print_r($fql);			
//print_r("\n\r");
			$result = $this -> fb -> getCurlFQL($fql, $args[0]);
			$this -> testCounter = $this -> testCounter + count($result -> event);
//print_r("Result count: " .  count($result -> event) . "\n\r");
			if (count($result -> event) > 0) {
				foreach ($result -> event as $key => $ev) {
					$this -> publishToBroker($ev, $args, $query['name']);
				}
				
				if ((count($chunked) - 1) > $currentChunk) {
					$currentChunk++;
				} else {
					$start = false;
				}
//print_r("Current chunk: " . $currentChunk . "\n\r");				
			} else {
				if ($result -> error_code && $result -> error_msg) {
					print_r($query['name'] . "\n\r");
					print_r($result);
					print_r($args);
					print_r("\n\r");
					print_r("\n\r");
					print_r("\n\r");
					
					if ((count($chunked) - 1) > $currentChunk) {
						$currentChunk++;
					} else {
						$start = false;
					}
				} else {
					if ((count($chunked) - 1) > $currentChunk) {
						$currentChunk++;
					} else {
						$start = false;
					}
				}
			}
		} while ($start !== false);
//print_r("\n\r");		
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
