<?php

namespace Models;

class Location extends \Library\Model
{
	public $id;
	public $facebook_id;
	public $city;
	public $state;
	public $country;
	public $alias;
	public $latitude;
	public $longitude;
	public $latitudeMin;
	public $longitudeMin;
	public $latitudeMax;
	public $longitudeMax;
	public $parent_id = 0;

	public $cacheData;

	public function initialize()
	{
		parent::initialize();		
		
		$this -> hasMany('id', '\Models\Member', 'location_id', array('alias' => 'member'));
		$this -> hasMany('id', '\Models\Event', 'location_id', array('alias' => 'event'));
		$this -> hasMany('id', '\Models\Venue', 'location_id', array('alias' => 'venue'));
		
		$this -> cacheData = $this -> getDI() -> get('cacheData');
	}
	
	
	public function setCache()
	{
		$query = new \Phalcon\Mvc\Model\Query("SELECT id, latitudeMin, longitudeMin, latitudeMax, longitudeMax, city, country FROM Models\Location", $this -> getDI());
		$locations = $query -> execute();
		$locationsCache = array();
	
		if ($locations) {
			foreach ($locations as $loc) {
				$locationsCache[$loc -> id] = array('latMin' => $loc -> latitudeMin,
						'lonMin' => $loc -> longitudeMin,
						'latMax' => $loc -> latitudeMax,
						'lonMax' => $loc -> longitudeMax,
						'city' => $loc -> city,
						'country' => $loc -> country);
			}
		}
		$this -> cacheData -> save('locations', $locationsCache);
	}
	

	public function createOnChange($argument = array(), $network = 'facebook')
	{
		$geo = $this -> getDi() -> get('geo');
		$isGeoObject = false;
		$newLoc = array();
		
		if (empty($argument)) {
			$argument = $geo -> getLocation();
			if ($argument) {
				$isGeoObject = true;
			}
		}
		$query = [];

		if (isset($argument['longitude']) && isset($argument['latitude'])) {
			$query[] = 'longitudeMin <= ' .  (float)$argument['longitude'];
			$query[] = (float)$argument['longitude'] . ' <= longitudeMax';
			$query[] = 'latitudeMin <= ' .  (float)$argument['latitude'];
			$query[] = (float)$argument['latitude'] . ' <= latitudeMax';
		} elseif (isset($argument['city']) && isset($argument['country'])) {
			$query[] = 'city LIKE "%' . trim($argument['city']) . '%"';
			$query[] = 'country LIKE "%' . trim($argument['country']) . '%"';
		}

		$query = implode(' and ', $query);
        if (!empty($query)) {
            $isLocationExists = self::findFirst($query);
        } else {
            $isLocationExists = false;
        }

		if (!$isLocationExists && isset($argument['longitude']) && isset($argument['latitude'])) {
			if (!$isGeoObject) {
				if (isset($argument['longitude']) && isset($argument['latitude'])) {
					$newLoc = $geo -> getLocation($argument);
				}				
			} else {
				$newLoc = $argument;
			}
			
			if ($newLoc) {
				if (!isset($argument['id']) || empty($argument['id'])) {
					$newLoc[$network . '_id'] = null;
				} else {
					$newLoc[$network . '_id'] = $argument['id'];
				}
			}
			
			if (!empty($newLoc)) {
				$this -> assign($newLoc);
				$this -> save();

				$isLocationExists = $this;
			}
		}

		if (!empty($newLoc)) {
			$isLocationExists -> latitude = $newLoc['latitude'];
			$isLocationExists -> longitude = $newLoc['longitude'];
			$isLocationExists -> latitudeMin = (float)$isLocationExists -> latitudeMin;
			$isLocationExists -> latitudeMax = (float)$isLocationExists -> latitudeMax;
			$isLocationExists -> longitudeMin = (float)$isLocationExists -> longitudeMin;
			$isLocationExists -> longitudeMax = (float)$isLocationExists -> longitudeMax;
		} elseif (!empty($argument) && isset($argument['resultSet'])) {
			$isLocationExists -> latitude = (float)$argument['latitude'];
			$isLocationExists -> longitude = (float)$argument['longitude'];
		}
	
		return $isLocationExists;
	} 
}
