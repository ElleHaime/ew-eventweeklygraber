<?php

namespace Jobs\Grabber\Parser;

use Models\VenueTag,
	Models\VenueCategory,
	Models\VenueImage,
	Models\Cron,
	Models\Venue,
	Models\Location;


class FacebookVenue
{
	use \Jobs\Grabber\Parser\Helper;
		
	protected $_di;


	public function __construct(\Phalcon\DI $dependencyInjector)
	{
        $this -> config = $dependencyInjector -> get('config');
        $this->_di = $dependencyInjector;
	}

	
	public function run(\AMQPEnvelope $data)
	{
		$msg = unserialize($data -> getBody());
		$venue = $msg['item'];
		$vObject = Venue::findFirst('fb_uid = "' . $venue['id'] . '"');
		

		if (isset($venue['category_list'])) $this -> parseCategories($vObject -> id, $venue['category_list']);
		if (isset($venue['hours'])) $vObject -> worktime = $this -> parseWorkingHours($venue['hours']);
		if (isset($venue['about'])) $vObject -> intro = $this -> prepareText($venue['about']);
		if (isset($venue['description'])) $vObject -> description = $this -> prepareText($venue['description']);
		if (isset($venue['phone'])) $vObject -> phone = $venue['phone'];
		if (isset($venue['website'])) $vObject -> site = $venue['website'];
		if (isset($venue['username'])) $vObject -> fb_username = $venue['username'];
		if (isset($venue['link'])) $vObject -> fb_url = $venue['link'];
		if (isset($venue['price_range'])) $vObject -> pricerange = $venue['price_range'];
 		if (isset($venue['cover'])) $this -> saveVenueImage('fb', $venue['cover'] -> source, $vObject, 'cover');
		if (isset($venue['photos'])) $this -> parsePhotos($vObject, $venue['photos']['data']);
		if (isset($venue['restaurant_services'])) $vObject -> services = serialize($venue['restaurant_services']);
		if (isset($venue['restaurant_specialties'])) $vObject -> specialties = serialize($venue['restaurant_specialties']);
		if (isset($venue['payment_options'])) $vObject -> payment = $this -> parseBoolean($venue['payment_options']);
		if (isset($venue['parking'])) $vObject -> parking = $this -> parseBoolean($venue['parking']);
		if (isset($venue['logo'])) {
			$vObject -> logo = $this -> getImageName($venue['logo'], $venue['name']);
			$this -> saveVenueImage('fb', $venue['logo'], $vObject);
		}
print_r($vObject -> id . "::" . $vObject -> fb_uid . "::" . $vObject -> fb_username . "\n\r");		
		$vObject -> save();
	}
	
	
	private function parseWorkingHours($arg)
	{
		$result = $diff = $list = [];
		
		foreach ($arg as $key => $val) {
			$day = explode('_', $key);
			$result[$day[2]][$val][] = $day[0];
		}

		foreach ($result['open'] as $mkey => $mval) {
			foreach ($result['close'] as $skey => $sval) {
				$days = array_intersect($mval, $sval);

				if (!empty($days)) {
					$days = array_values($days);
					$diff['open'][ucfirst($days[0]) . '-' . ucfirst($days[count($days)-1])] = $mkey;
					$diff['close'][ucfirst($days[0]) . '-' . ucfirst($days[count($days)-1])] = $skey;
				}
			}	
		}
		
		foreach ($diff['open'] as $key => $val) {
			$days = explode('-', $key);
			$days[0] == $days[1] ? $days = $days[0] : $days = $key;
			$list[$days] = $val . '-' . $diff['close'][$key];
		}
		
		return serialize($list);
	} 
	
	
	
	private function parseCategories($venueId, $params = [])
	{
		$result = [];
		
		foreach ($params as $key => $val) {
			$result[] = $val -> name;
		}
		$this -> categorizeObject($venueId, $result, 'venue');
		
		return;
	}
	
	
	private function parsePhotos($venueObject, $params = [])
	{
		if (!empty($params)) {
			foreach ($params as $key => $val) {
				$this -> saveVenueImage('fb', $val -> images[0] -> source, $venueObject, 'gallery');
			}
		}
		
		return;
	}
	
	
	private function parseBoolean($params = [])
	{
		$result = [];
		foreach ($params as $key => $val) {
			if ($val == 1) $result[$key] = $val;
		}
		
		if (empty($result))  {
			return null;
		} else {
			return serialize($result);
		}
	}
}