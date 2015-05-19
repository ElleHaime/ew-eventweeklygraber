<?php 

namespace Models;

class EventRating extends \Library\Model
{
	public $id;
	public $event_id;
	public $location_id;
	public $rank = 0; 
	
	public function initialize()
	{
		parent::initialize();
				
        $this -> belongsTo('event_id', '\Objects\Event', 'id', array('alias' => 'event_rating'));
	}
	
	public function deleteEventRating($event)
	{
		$events = self::findFirst(['event_id = "' . $event . '"']);
		if ($events) {
			$events -> delete();
		}
	
		return;
	}
}