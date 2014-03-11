<?php

namespace Models;

class Location extends \Phalcon\Mvc\Model
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
	 

	public function initialize()
	{
		$this -> hasMany('id', '\Models\Member', 'location_id', array('alias' => 'member'));
		$this -> hasMany('id', '\Models\Event', 'location_id', array('alias' => 'event'));
		$this -> hasMany('id', '\Models\Venue', 'location_id', array('alias' => 'venue'));
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

		if (isset($argument['longitude'])) {
			$query[] = 'longitudeMin <= ' .  (float)$argument['longitude'];
			$query[] = (float)$argument['longitude'] . ' <= longitudeMax';
		}
		if (isset($argument['latitude'])) {
			$query[] = 'latitudeMin <= ' .  (float)$argument['latitude'];
			$query[] = (float)$argument['latitude'] . ' <= latitudeMax';
		}

        if (!empty($query)) {
            $isLocationExists = self::findFirst($query);
        }else {
            $isLocationExists = false;
        }


		if (!$isLocationExists) {
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
		} else {
			$isLocationExists -> latitude = (float)$argument['latitude'];
			$isLocationExists -> longitude = (float)$argument['longitude'];
		}
		$isLocationExists -> latitudeMin = (float)$isLocationExists -> latitudeMin;
		$isLocationExists -> latitudeMax = (float)$isLocationExists -> latitudeMax;
		$isLocationExists -> longitudeMin = (float)$isLocationExists -> longitudeMin;
		$isLocationExists -> longitudeMax = (float)$isLocationExists -> longitudeMax;
	
		return $isLocationExists;
	} 
}
