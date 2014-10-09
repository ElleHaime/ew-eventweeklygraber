<?php

namespace Models;

class Grabber extends \Phalcon\Mvc\Model
{
	public $id;
	public $grabber;
	public $param;
	public $value;
	public $last_id;
	
	public function initialize()
	{
		$this -> belongsTo('location_id', '\Models\Location', 'id', array('alias' => 'location',
																	 	   'baseField' => 'alias'));
	}
}
