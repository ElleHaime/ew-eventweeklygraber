<?php

namespace Models;

class EventMember extends \Library\Model
{
	const
	JOIN    = 1,
	MAYBE   = 2,
	DECLINE = 3;

	public $id;
	public $event_id;
	public $member_id;
	public $member_status;

	public function initialize()
	{
		parent::initialize();		
		
		//$this -> hasMany('event_id', '\Models\Event', 'id', array('alias' => 'eventpart'));
		$this -> hasMany('member_id', '\Models\Member', 'id', array('alias' => 'memberpart'));
	}
	
	public function getEventMemberEventsCount($uId)
	{
		if ($uId) {
			$query = new \Phalcon\Mvc\Model\Query("SELECT Models\Event.id, Models\Event.fb_uid
													FROM Models\Event
														LEFT JOIN Models\EventMember ON Models\Event.id = Models\EventMember.event_id
													WHERE Models\Event.deleted = 0
														AND Models\Event.event_status = 1
														AND Models\Event.start_date > '" . date('Y-m-d H:i:s', strtotime('today -1 minute')) . "'
														AND Models\EventMember.member_status = 1
														AND Models\EventMember.member_id = " . $uId, $this -> getDI());
			$event = $query -> execute();
	
			return $event;
		} else {
			return 0;
		}
	}
	
	public function deleteEventJoined($event)
	{
		$events = self::findFirst(['event_id = "' . $event . '"']);
		if ($events) {
			foreach ($events -> $ev) {
				$ev -> delete();
			}
		}
	
		return;
	}
}
