<?php

namespace Library;

class Geo extends \Phalcon\Mvc\User\Plugin
{
	const DEFAULT_RADIUS_DIFF = 10;
	protected $_unitTypes = ['locality',
							  	'colloquial_area',
							  	'sublocality',
								'sublocality_level_5',
								'sublocality_level_4',
								'sublocality_level_3',
								'sublocality_level_2',
								'sublocality_level_1',
								'postal_code',
								'administrative_area_level_5',
								'administrative_area_level_4',
								'administrative_area_level_3',
								'administrative_area_level_2',
								'administrative_area_level_1',
								'country'];
	protected $_errors		= [];
	protected $_apiUrl 	= 'http://maps.googleapis.com/maps/api/geocode/json?language=en&';
	
	
	public function getLocation($coordinates = array())
	{
// print_r("\n\r");		
// print_r($coordinates);
// print_r("\n\r");
		$location = false;

		if (!empty($coordinates)) 
		{
			$localityScope = [];
			$units = [];
			$baseType = 'locality';
		
			if (!empty($coordinates['city']) && !empty($coordinates['country'])) 
			{
				$queryParams = ['locality:' . urlencode(trim($coordinates['city'], "'")), 'country:' . urlencode($coordinates['country'])];
				$url = $this -> _apiUrl . 'components=' . implode('|', $queryParams);
				$result = json_decode(file_get_contents($url));
// print_r($url);
// print_r("\n\r");
// die();
// print_r($result);
// print_r("\n\r");
				if ($result -> status == 'OK' && count($result -> results) > 0) 
				{
					if (count($result -> results) == 1) 
					{
						$localityScope = $result -> results[0];
						$baseType = 'locality';
					} else {
						foreach ($result -> results as $key => $scope) {
							if ($this -> isInGeometry($coordinates['latitude'], $coordinates['longitude'], $scope -> geometry -> viewport)) {
								foreach ($scope -> address_components as $area) {
									if (in_array('locality', $area -> types) && $area -> long_name == $coordinates['city']) {
										$localityScope = $scope;
										$baseType = 'locality';
										
										break;
									}
								}
							}
						}
					}							
				} 			
			} 
			
			if (empty($localityScope) && isset($coordinates['latitude']) && isset($coordinates['longitude'])) 
			{
				$queryParams = $this -> _buildQuery($coordinates['latitude'], $coordinates['longitude']);
				$url = $this -> _apiUrl . $queryParams;
				$result = json_decode(file_get_contents($url));
// print_r($url);
// print_r("\n\r");
// print_r($result);
// print_r("\n\r");
				if ($result -> status == 'OK' && count($result -> results) > 0) 
				{
					foreach ($result -> results as $object => $details) 
					{
						if (in_array($details -> types[0], $this -> _unitTypes)) $units[$details -> types[0]] = $object;
					}
					if (!empty($units)) {
						$firstUnit = array_keys($units)[0];
						$baseType = $firstUnit;
						$localityScope = $result -> results[$units[$firstUnit]];
					} else {
						return false;
					}
				}	
			}
// print_r("\n\r");
// print_r("\n\r");
// print_r($localityScope);
// print_r("\n\r");
			if (!empty($localityScope)) 
			{
				foreach ($localityScope -> address_components as $obj => $lvl) 
				{
					if (in_array($baseType, $lvl -> types)) 
					{
						$location['alias'] = $lvl -> long_name;
						$location['city'] = $lvl -> long_name;
					}
					if (in_array('administrative_area_level_1', $lvl -> types)) 
					{
						$location['state'] = $lvl -> long_name;
						if (!isset($location['city'])) 
						{
							$location['alias'] = $lvl -> long_name;
							$location['city'] = $lvl -> long_name;
						}
					}
					if (in_array('country', $lvl -> types)) $location['country'] = $lvl -> long_name;
				}

				if (isset($location['city']) && isset($location['country'])) 
				{
					if (!empty($coordinates)) 
					{
						$location['latitude'] = (float)$coordinates['latitude'];
						$location['longitude'] = (float)$coordinates['longitude'];
					}

					if (isset($location['latitude']) && isset($location['longitude'])) 
					{
						$location['latitudeMin'] = (float)$localityScope -> geometry -> viewport -> southwest -> lat;
						$location['longitudeMin'] = (float)$localityScope -> geometry -> viewport -> southwest -> lng;
						$location['latitudeMax'] = (float)$localityScope -> geometry -> viewport -> northeast -> lat;
						$location['longitudeMax'] = (float)$localityScope -> geometry -> viewport -> northeast -> lng;
						
						$location['resultSet'] = true;
					} 
				}
			}
		}
// print_r("\n\r");
// print_r($location);
// print_r("\n\r");
// die();		
		return $location;
	}		
	
	
	protected function _buildQuery($lat, $lon, $countryCode = false)
	{
		$result = array();

		if ($countryCode) $result[] = 'region=' . $this -> _countryCode;

		if ($lat && $lon) $result[] = 'latlng=' . $lat . ',' . $lon;
		
		return implode("&", $result);
	}

	
	public function getErrors()
	{
		return $this -> _errors;
	}
	
	
	protected function isInGeometry($lat, $lng, $geometry) 
	{
		$result = false;

		if ($geometry -> northeast -> lat >= $lat && $geometry -> southwest -> lat <= $lat &&
			$geometry -> northeast -> lng >= $lng && $geometry -> southwest -> lng <= $lng) 
			$result = true;
		
		return $result;
	}

	
	public function makeRequest($city, $state, $country, $formatted = true)
	{
		$data = false; 
		
		$queryParams = ['locality:' . urlencode(trim($city, "'")), 'country:' . urlencode($country), 'administrative_area:' . urlencode($state)];
		$url = $this -> _apiUrl . 'components=' . implode('|', $queryParams);
		$result = json_decode(file_get_contents($url));
		
		switch ($result -> status) {
			case 'OK':
					if (count($result -> results) > 0) {
						if ($formatted) {
							foreach ($result -> results[0] -> address_components as $area) {
								if (in_array('locality', $area -> types)) {
									$data['locality'] = $area;
									break;
								}
							}
							$data['geometry'] = $result -> results[0] -> geometry -> viewport;
							$data['place_id'] = $result -> results[0] -> place_id;
						} else {
							$data = $result -> results;
						}
					}
				break;
			
			default:
					throw new \Exception($result -> error_message);
				break;
		}
		
		return $data;
	}
}
