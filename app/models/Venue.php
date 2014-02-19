<?php 

namespace Models;


class Venue extends \Phalcon\Mvc\Model
{
	public $id;
	public $fb_uid;
	public $location_id;
	public $name;
	public $address;	
	public $coordinates;
	public $latitude;  	
	public $longitude;

	public $needCache = true;

	public function initialize()
	{
		$this -> belongsTo('location_id', '\Models\Location', 'id', array('alias' => 'location'));
		$this -> hasOne('id', '\Models\Event', 'venue_id', array('alias' => 'event'));
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