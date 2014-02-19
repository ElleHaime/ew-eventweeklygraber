<?php 

namespace Models;

class EventLike extends \Phalcon\Mvc\Model
{
	public $id;
    public $event_id;
    public $member_id;
    public $status;
	
	public function initialize()
	{
        $this->belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event_like'));
        $this->belongsTo('member_id', '\Models\Member', 'id', array('alias' => 'event_like'));
    }
}