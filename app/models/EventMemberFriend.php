<?php

namespace Models;

class EventMemberFriend extends \Phalcon\Mvc\Model
{
	public $id;
	public $event_id;
	public $member_id;


	public function initialize()
	{
		$this -> hasMany('event_id', '\Models\Event', 'id', array('alias' => 'eventfriendart'));
		$this -> hasMany('member_id', '\Models\Member', 'id', array('alias' => 'memberfriendpart'));
	}
}