<?php 

namespace Models;

class EventImage extends \Phalcon\Mvc\Model
{
    use \Sharding\Core\Env\Phalcon;	
	
	public $id;
	public $event_id;
	public $image;
	public $type;

	public function initialize()
	{
		$this -> belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event'));
	}
}