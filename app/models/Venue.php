<?php 

namespace Models;


class Venue extends \Library\Model
{
	public $id;
	public $fb_uid;
	public $eb_uid;
	public $eb_url;
	public $location_id;
	public $name;
	public $address;	
	public $coordinates;
	public $latitude;  	
	public $longitude;


	public function initialize()
	{
		parent::initialize();		
		
		$this -> belongsTo('location_id', '\Models\Location', 'id', array('alias' => 'location'));
		$this -> hasOne('id', '\Models\Event', 'venue_id', array('alias' => 'event'));
	}

	
	public function getCreators($location_id = false)
	{
		if ($location_id !== false ) {
			$query = new \Phalcon\Mvc\Model\Query("SELECT id, fb_uid FROM Models\Venue
														WHERE fb_uid is not null and location_id = " . $location_id, 
												$this -> getDI());
		} else {
			$query = new \Phalcon\Mvc\Model\Query("SELECT id, fb_uid FROM Models\Venue
														WHERE fb_uid is not null 
														GROUP BY fb_uid", $this -> getDI());
		}
		$creators = $query -> execute();
		
		return $creators;
	}

	
	public function setCache()
	{
	}
}