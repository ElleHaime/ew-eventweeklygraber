<?php

namespace Jobs\Grabber\Sync;

class Expired
{
	public $di;
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	public function run()
	{
		$events = [];
		$query = new \Phalcon\Mvc\Model\Query("SELECT Models\Event.id
												FROM Models\Event
												WHERE Models\Event.end_date < '" . date('Y-m-d H:i:s', strtotime('midnight')) . "'", $this -> di);
		$result = $query -> execute();
		foreach($result as $ev) {
			$events[] = $ev -> id;
		}
		
		if (!empty($events)) {
			$query = new \Phalcon\Mvc\Model\Query("DELETE FROM Models\EventLike 
													WHERE Models\EventLike.event_id IN(" . implode(',', $events) . ")", $this -> di);
			try {
				$query -> execute();
			} catch(\Phalcon\Exception $e) {
				print_r($e -> getMessage() . "\n\r");
			} 
			
			$query = new \Phalcon\Mvc\Model\Query("DELETE FROM Models\EventMember
													WHERE Models\EventMember.event_id IN(" . implode(',', $events) . ")", $this -> di);
			try {
				$query -> execute();
			} catch(\Phalcon\Exception $e) {
				print_r($e -> getMessage() . "\n\r");
			} 
			
			$query = new \Phalcon\Mvc\Model\Query("DELETE FROM Models\EventMemberFriend
													WHERE Models\EventMemberFriend.event_id IN(" . implode(',', $events) . ")", $this -> di);
			try {
				$query -> execute();
			} catch(\Phalcon\Exception $e) {
				print_r($e -> getMessage() . "\n\r");
			} 
		}
		
		$eventCounters = new \Models\EventMemberCounter();
		$eventCounters -> syncMemberCounter(); 
	}
}