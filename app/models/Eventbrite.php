<?php

namespace Models;

class Eventbrite extends \Phalcon\Mvc\Model
{
	public $id;
	public $location;
	public $last_id;
	
	public function initialize()
	{
		$this -> belongsTo('location_id', '\Models\Location', 'id', array('alias' => 'location',
																	 	   'baseField' => 'alias'));
	}
}
