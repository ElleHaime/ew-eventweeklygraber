<?php

namespace Jobs\Grabber\Sync;

class Expired
{
	public function run()
	{
		$di = $this -> getDI();
		
		$query = new \Phalcon\Mvc\Model\Query("SELECT Models\Event.id
												FROM Models\Event
												WHERE Models\Event.end_date < " . date('Y-m-d H:i:s', strtotime('tomorrow midnight')), $di);
		$events = $query -> execute() -> toArray();
		
		if (!empty($events)) {
			$query = new \Phalcon\Mvc\Model\Query("DELETE FROM Models\EventLike 
													WHERE Models\EventLike.event_id IN()(" . implode(',', $events) . ")", $di);
			try {
				$query -> execute();
			} catch(\Phalcon\Exception $e) {
				print_r($e -> getMessage());
			} 
			
			$query = new \Phalcon\Mvc\Model\Query("DELETE FROM Models\EventMember
													WHERE Models\EventMember.event_id IN(" . implode(',', $events) . ")", $di);
			try {
				$query -> execute();
			} catch(\Phalcon\Exception $e) {
				print_r($e -> getMessage());
			} 
			
			$query = new \Phalcon\Mvc\Model\Query("DELETE FROM Models\EventMemberFriend
													WHERE Models\EventMemberFriend.event_id IN(" . implode(',', $events) . ")", $di);
			try {
				$query -> execute();
			} catch(\Phalcon\Exception $e) {
				print_r($e -> getMessage());
			} 
		}
		
		$eventCounters = new \Models\EventMemberCounter();
		$eventCounters -> syncMemberCounter();
	}
}