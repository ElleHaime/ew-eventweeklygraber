<?php

namespace Models;

class Featured extends \Library\Model
{
	const OBJECT_TYPE = 'event';
	
	public $id;
	public $object_type;
	public $object_id;
	public $priority;
	public $location_id;
	
	
	public function deleteEventFeatured($event)
	{
		$events = self::find(['object_id = "' . $event . '" AND object_type = "' . self::OBJECT_TYPE . '"']);
		if ($events) {
			foreach ($events as $ev) {
				$ev -> delete();
			}
		}
	
		return;
	}
}