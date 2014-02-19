<?php

namespace Library;

class Geo extends \Phalcon\Mvc\User\Plugin
{
	const DEFAULT_RADIUS_DIFF = 10;
	protected $_unitTypes = array('locality',
								  'administrative_area_level_3',
								  'administrative_area_level_2',
								  'administrative_area_level_1',
								  'country');
	protected $_errors		= array();


	public function getLocation($coordinates = array())
	{
		if (!empty($coordinates)) {
			$queryParams = $this -> _buildQuery($coordinates['latitude'], $coordinates['longitude']); 

			if ($queryParams != '') {	
				$units = array();

				$url = 'http://maps.googleapis.com/maps/api/geocode/json?' . $queryParams. '&sensor=false&language=en';
				$result = json_decode(file_get_contents($url));

				if ($result -> status == 'OK' && count($result -> results) > 0) {
					foreach ($result -> results as $object => $details) {
						$units[$details -> types[0]] = $object;
					}

					if (empty(array_intersect(array_keys($units), $this -> _unitTypes))) {
						$newArgs = $result -> results[0];
				
						foreach ($newArgs -> address_components as $objNew => $lvlNew) {
							if ($lvlNew -> types[0] == 'locality') {
								$newComponents[] = 'locality:' . str_replace(' ', '+', $lvlNew -> short_name);
							}
							if ($lvlNew -> types[0] == 'administrative_area_level_1') {
								$newComponents[] = 'administrative_area:' . str_replace(' ', '+', $lvlNew -> short_name);
							}
							if ($lvlNew -> types[0] == 'country') {
								$newComponents[] = 'country:' . str_replace(' ', '+', $lvlNew -> short_name);
							}
						}
						$url = 'http://maps.googleapis.com/maps/api/geocode/json?components=' . implode('|', $newComponents) . '&sensor=false&language=en';
						$result = json_decode(file_get_contents($url));
			
						if ($result -> status == 'OK' && count($result -> results) > 0) {
							foreach ($result -> results as $object => $details) {
								$units[$details -> types[0]] = $object;
							}
						}
					}

					if (isset($units['locality'])) {
						$scope = $result -> results[$units['locality']];
						$baseType = 'locality';
					} elseif (isset($units['administrative_area_level_3'])) {
						$scope = $result -> results[$units['administrative_area_level_3']];
						$baseType = 'administrative_area_level_3';
					} elseif (isset($units['administrative_area_level_2'])) {
						$scope = $result -> results[$units['administrative_area_level_2']];
						$baseType = 'administrative_area_level_2';
					} 
	 
					if (isset($scope)) {			
						foreach ($scope -> address_components as $obj => $lvl) {
		
							if ($lvl -> types[0] == $baseType) {
								$location['alias'] = $lvl -> long_name;
								$location['city'] = $lvl -> long_name;
							}
							if ($lvl -> types[0] == 'administrative_area_level_1') {
								$location['state'] = $lvl -> long_name;
							}
							if ($lvl -> types[0] == 'country') {
								$location['country'] = $lvl -> long_name;
							}
						}
	   
						if (isset($location['city']) && isset($location['country'])) {
							if (!empty($coordinates)) {
								$location['latitude'] = (float)$coordinates['latitude'];
								$location['longitude'] = (float)$coordinates['longitude'];
							} 

							if (isset($location['latitude']) && isset($location['longitude']) && !empty($result -> results[0] -> geometry)) {
								$location['latitudeMin'] = (float)$scope -> geometry -> bounds -> southwest -> lat;
								$location['longitudeMin'] = (float)$scope -> geometry -> bounds -> southwest -> lng;
								$location['latitudeMax'] = (float)$scope -> geometry -> bounds -> northeast -> lat;
								$location['longitudeMax'] = (float)$scope -> geometry -> bounds -> northeast -> lng;
							}						
							return $location;
						} 
					} else {
						return false;
					}
					
				} else {
				 	$return = $result -> status;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	} 
	
	protected function _buildQuery($lat, $lon, $countryCode = false)
	{
		$result = array();


		if ($countryCode) {
			$result[] = 'region=' . $this -> _countryCode;
		}
		if ($lat && $lon) {
			$result[] = 'latlng=' . $lat . ',' . $lon;
		}
		
		return implode("&", $result);
	}

	public function getErrors()
	{
		return $this -> _errors;
	}
}
