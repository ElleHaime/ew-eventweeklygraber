<?php

namespace Models;

class Featured extends \Library\Model
{
	public $id;
	public $object_type;
	public $object_id;
	public $priority;
	public $location_id;
	
	
	public function deleteEventFeatured($event)
	{
		$events = self::find(['event_id = "' . $event . '"']);
		if ($events) {
			foreach ($events as $ev) {
				$ev -> delete();
			}
		}
	
		return;
	}
}