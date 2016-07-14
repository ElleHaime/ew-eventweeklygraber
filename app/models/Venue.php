<?php 

namespace Models;


class Venue extends \Library\Model
{
	public $id;
	public $fb_uid;
	public $fb_username;
	public $eb_uid;
	public $eb_url;
	public $location_id;
	public $name;
	public $address;	
	public $site;
	public $logo;
	public $latitude;  	
	public $longitude;
	public $intro;
	public $description;
	public $worktime;
	public $phone;
	public $email;
	public $transit;
	public $pricerange;
	public $services;
	public $specialties;
	public $payment;
	public $parking;


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

	
	public function beforeDelete()
	{
		// update events
		$obj = (new \Models\Event()) -> setShardByCriteria($this -> location_id);
		$events = $obj::find('venue_id = ' . $this -> id);
		foreach ($events as $e) {
			$e -> setShardByCriteria($e -> location_id);
			$e -> venue_id = null;
			$e -> update();
			
			$grid = new \Models\Event\Grid\Search\Event(['location' => $e -> location_id], $this -> getDI(), null, ['adapter' => 'dbMaster']);
			$indexer = new \Models\Event\Search\Indexer($grid);
			$indexer -> setDi($this -> getDI());
			$indexer -> updateData($e -> id);
		}
		
		return;
	}
	
	
	public function setCache()
	{
	}
}