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
		$this -> getReadConnection() -> query("DELETE FROM " . $this -> getSource() . " WHERE object_type = '" . self::EVENT_OBJECT_TYPE . "' AND object_id  = '" . $eventId . "'");
	
		return;
	}
	
	
	public function transferInShards($relationName, $oldObject, $parentId)
	{
		if ($current = $oldObject -> $relationName) {
	
			foreach ($current as $obj) {
				$obj -> object_id = $parentId;
				$obj -> update();
			}
		}
	
		return;
	}
}