<?php

namespace Models;

class EventMemberCounter extends \Phalcon\Mvc\Model
{
	public $member_id;
	public $userEventsLiked = 0;
	public $userEventsGoing = 0;
	public $userEventsCreated = 0;
	public $userFriendsGoing = 0;


	public function initialize()
	{
		$this -> belongsTo('member_id', '\Models\Member', 'id', array('alias' => 'counters'));
	}
	
	public function syncMemberCounter()
	{
		$di = $this -> getDI();
		$query = new \Phalcon\Mvc\Model\Query("SELECT Frontend\Models\Member.id FROM Frontend\Models\Member", $di);
		$members = $query -> execute();
	
		if ($members) {
			foreach ($members as $member) {
				$memberId = $member -> member_id;
				$query = new \Phalcon\Mvc\Model\Query("SELECT DISTINCT Models\Event.id
														FROM  Models\Event
														WHERE Models\Event.member_id = " . $memberId, $di);
				$created = $query -> execute() -> count();
				 
				$query = new \Phalcon\Mvc\Model\Query("SELECT DISTINCT Models\EventMember.event_id
														FROM  Models\EventMember
														WHERE Models\EventMember.member_status = 1
														AND Models\EventMember.member_id = " . $memberId, $di);
				$going = $query -> execute() -> count();
				 
				$query = new \Phalcon\Mvc\Model\Query("SELECT DISTINCT Models\EventLike.event_id
														FROM  Models\EventLike
														WHERE Models\EventLike.status = 1
														AND Models\EventLike.member_id = " . $memberId, $di);
				$liked = $query -> execute() -> count();
				 
				$query = new \Phalcon\Mvc\Model\Query("SELECT DISTINCT Models\EventMemberFriend.event_id
														FROM  Models\EventMemberFriend
														WHERE Models\EventMemberFriend.member_id = " . $memberId, $di);
				$friends = $query -> execute() -> count();
				 
				$counters = self::findFirst('member_id = ' . $memberId);
				if ($counters) {
					$upCounter = $counters;
				} else {
					$upCounter = new self;
					$upCounter -> assign(['member_id' => $memberId]);
				}
	
				$upCounter -> assign(['userEventsCreated' => $created,
									  'userEventsLiked' => $liked,
									  'userEventsGoing' => $going,
									  'userFriendsGoing' => $friends]);
				$upCounter -> save();
			}
		}
	
		print_r("Counters synced\n\r");
	}
}