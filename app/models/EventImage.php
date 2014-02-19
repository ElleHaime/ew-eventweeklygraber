<?php 

namespace Models;

class EventImage extends \Phalcon\Mvc\Model
{
	public $id;
	public $event_id;
	public $image;

	public function initialize()
	{
		$this -> belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event'));
	}
}