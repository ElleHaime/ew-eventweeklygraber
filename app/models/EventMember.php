<?php

namespace Models;

class EventMember extends \Phalcon\Mvc\Model
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
		$this -> hasMany('event_id', '\Models\Event', 'id', array('alias' => 'eventpart'));
		$this -> hasMany('member_id', '\Models\Member', 'id', array('alias' => 'memberpart'));
	}
}
