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
			$originAddress = [];
			$queryParams = $this -> _buildQuery($coordinates['latitude'], $coordinates['longitude']);
			
			if (!empty($coordinates['zip']) && !empty($coordinates['city']) && !empty($coordinates['country'])) {
				$originAddress = ['postal_code' => $coordinates['zip'],
									'locality' => $coordinates['city'],
									'country' => $coordinates['country']];
			} 
	
			if ($queryParams != '') {
				$localityLevel = false;
	
				$url = 'http://maps.googleapis.com/maps/api/geocode/json?' . $queryParams. '&sensor=false&language=en';
				$result = json_decode(file_get_contents($url));

				if ($result -> status == 'OK' && count($result -> results) > 0) {
					if (!empty($originAddress)) {
						foreach ($result -> results as $index => $scope) {
							if ($localityLevel = $this -> compareAddressComponents($originAddress, $scope -> address_components)) {
								$localityScope = $scope;
								
								break;
							}
						}
					} else {
						foreach ($result -> results as $index => $scope) {
							if ($scope -> types[0] == 'postal_code') {
								$localityLevel = $scope -> address_components;
								$localityScope = $scope;
								
								break;
							}							
						}
					}	
					
					if (isset($localityLevel)) {
						foreach ($localityLevel as $obj => $lvl) {
							if ($lvl -> types[0] == 'locality') {
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
	
							if (isset($location['latitude']) && isset($location['longitude']) && !empty($scope -> geometry)) {
								$location['latitudeMin'] = (float)$scope -> geometry -> bounds -> southwest -> lat;
								$location['longitudeMin'] = (float)$scope -> geometry -> bounds -> southwest -> lng;
								$location['latitudeMax'] = (float)$scope -> geometry -> bounds -> northeast -> lat;
								$location['longitudeMax'] = (float)$scope -> geometry -> bounds -> northeast -> lng;
								$location['resultSet'] = true;
							} else {
								$location['resultSet'] = false;
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
	
	
	protected function _buildQuery($lat, $lon)
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
	
	protected function compareAddressComponents($origin, $response)
	{
		$result = false;
		$intersections = 0;

		foreach ($response as $respKey => $respVal) {
			if ($intersections < count($origin)) {
				foreach ($origin as $origKey => $origVal) {
					if ($respVal -> types[0] == $origKey && $respVal -> long_name == $origVal) {
						$intersections++;
					}
				}
			} 
			
			if ($intersections >= count($origin)) {
				$result = $response;
			}
		}

		return $result;
	}
}
