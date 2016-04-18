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
				
        $this -> belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event_rating'));
	}
	
	public function deleteEventRating($eventId)
	{
		$this -> getReadConnection() -> query("DELETE FROM " . $this -> getSource() . " WHERE event_id = '" . $eventId . "'");
	
		return;
	}
}