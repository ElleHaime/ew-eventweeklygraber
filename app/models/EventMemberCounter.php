<?php

namespace Models;

class EventMemberCounter extends \Phalcon\Mvc\Model
{
	public $member_id;
	public $member_id;
	public $userEventsLiked = 0;
	public $userEventsGoing = 0;
	public $userEventsCreated = 0;
	public $userFriendsGoing = 0;


	public function initialize()
	{
		$this -> belongsTo('member_id', '\Models\Member', 'id', array('alias' => 'counters'));
	}
}