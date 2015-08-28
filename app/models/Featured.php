<?php

namespace Models;

class Featured extends \Library\Model
{
	const EVENT_OBJECT_TYPE = 'event';
	
	public $id;
	public $object_type;
	public $object_id;
	public $priority;
	public $location_id;
	
	
	public function deleteEventFeatured($eventId)
	{
// 		$query = new \Phalcon\Mvc\Model\Query("SELECT * FROM Models\Featured WHERE Models\Featured.object_type = '" . self::EVENT_OBJECT_TYPE . "' AND Models\Featured.object_id = '" . $eventId . "'" , $this -> getDI());
// 		$events = $query -> execute();
// 		if ($events) {
// 			foreach ($events as $obj) {
// 				//$obj -> delete();
// 			}
// 		}
		
		$this -> getReadConnection() -> query("DELETE FROM " . $this -> getSource() . " WHERE object_type = '" . self::EVENT_OBJECT_TYPE . "' AND object_id  = '" . $eventId . "'");
// 		$this -> getReadConnection() -> query("SELECT * FROM " . $this -> getSource() . " WHERE object_type = '" . self::EVENT_OBJECT_TYPE . "' AND object_id = '" . $eventId . "'");
		
		
	
		return;
	}
}