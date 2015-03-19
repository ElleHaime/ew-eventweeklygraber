<?php 

namespace Models;

class EventImage extends \Library\Model
{
    use \Sharding\Core\Env\Phalcon;	
	
	public $id;
	public $event_id;
	public $image;
	public $type;

	public function initialize()
	{
		parent::initialize();		
		
		$this -> belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event'));
	}
}