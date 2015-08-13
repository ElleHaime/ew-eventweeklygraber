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

	//public $cacheData;

	public function initialize()
	{
		parent::initialize();		
		
		$this -> belongsTo('location_id', '\Models\Location', 'id', array('alias' => 'location'));
		$this -> hasOne('id', '\Models\Event', 'venue_id', array('alias' => 'event'));
		
		//$this -> cacheData = $this -> getDI() -> get('cacheData');
	}

	
	public function setCache()
	{
		$query = new \Phalcon\Mvc\Model\Query("SELECT id, fb_uid, address, location_id, latitude, longitude FROM Models\Venue", $this -> getDI());
		$venues = $query -> execute();
	
		if ($venues) {
			foreach ($venues as $venue) {
			/*	$this -> cacheData -> save('venue_' . $venue -> fb_uid,
						array('venue_id' => $venue -> id,
								'address' => $venue -> address,
								'location_id' => $venue -> location_id,
								'latitude' => $venue -> latitude,
								'longitude' => $venue -> longitude)); */
			}
				
			//$this -> cacheData -> save('fb_venues', 'cached');
		}
	}
	
	
	public function createOnChange($argument = array())
	{
		$isVenueExists = false;
        $query = '';

		if (!empty($argument)) {
			if (isset($argument['latitude']) && isset($argument['longitude'])) {
				// find by coordinates
				$query = 'latitude = ' . (float)$argument['latitude'] . ' and longitude = ' . (float)$argument['longitude'];
				$isVenueExists = self::findFirst($query);
			} elseif (isset($argument['address']) && !empty($argument['address'])) {
				$query = 'address like "%' . $argument['address'] . '%"';
				$isVenueExists = self::findFirst($query);
			}
		}

		if (!$isVenueExists && $query != '') {
			$this -> assign($argument);
			$this -> save();
			
			return $this;
        }  elseif ($isVenueExists) {
			return  $isVenueExists;
		}  else {
            return  false;
        }
	}

}