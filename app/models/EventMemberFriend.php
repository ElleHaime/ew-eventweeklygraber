<?php

namespace Models;

class EventMemberFriend extends \Library\Model
{
	public $id;
	public $event_id;
	public $member_id;


	public function initialize()
	{
		parent::initialize();		
		
		$this -> hasMany('event_id', '\Models\Event', 'id', array('alias' => 'eventfriendart'));
		$this -> hasMany('member_id', '\Models\Member', 'id', array('alias' => 'memberfriendpart'));
	}
	
	public function getEventMemberFriendEventsCount($uId)
	{
		if ($uId) {
			$query = new \Phalcon\Mvc\Model\Query("SELECT Models\Event.id, Models\Event.fb_uid
													FROM Models\Event
														LEFT JOIN Models\EventMemberFriend ON Models\Event.id = Models\EventMemberFriend.event_id
													WHERE Models\Event.deleted = 0
														AND Models\Event.event_status = 1
														AND Models\Event.start_date > '" . date('Y-m-d H:i:s', strtotime('today -1 minute')) . "'
														AND Models\EventMemberFriend.member_id = " . $uId, $this -> getDI());
			$event = $query -> execute();
				
			return $event;
		} else {
			return 0;
		}
	}
	
	
	public function deleteEventFriend($eventId)
	{
		$events = self::find(['event_id = "' . $eventId . '"']);
		if ($events) {
			foreach ($events as $ev) {
				$ev -> delete();
			}
		}
	
		return;
	}
}