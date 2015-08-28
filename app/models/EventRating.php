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
	
	public function deleteEventRating($eventId)
	{
// 		$query = new \Phalcon\Mvc\Model\Query("SELECT * FROM Models\EventRating WHERE Models\EventRating.event_id = '" . $eventId . "'" , $this -> getDI());
// 		$events = $query -> execute();
// 		if ($events) {
// 			foreach ($events as $obj) {
// 				//$obj -> delete();
// 			}
// 		}

		$this -> getReadConnection() -> query("DELETE FROM " . $this -> getSource() . " WHERE event_id = '" . $eventId . "'");
// 		$this -> getReadConnection() -> query("SELECT * FROM " . $this -> getSource() . " WHERE event_id = '" . $eventId . "'");
	
		return;
	}
}