<?php

namespace Models;

class Grabber extends \Library\Model
{
	public $id;
	public $grabber;
	public $type;
	public $param;
	public $value;
	public $last_id;
	
/*	public function initialize()
	{
		parent::initialize();
				
		$this -> belongsTo('location_id', '\Models\Location', 'id', array('alias' => 'location',
																	 	   'baseField' => 'alias'));
	} */
}
