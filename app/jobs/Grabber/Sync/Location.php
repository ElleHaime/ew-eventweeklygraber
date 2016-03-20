<?php

namespace Jobs\Grabber\Sync;

use Models\Location as LocationModule,
	Models\Event;


class Location
{
	use \Jobs\Grabber\Parser\Helper;
	
	public $di;
	public $locScope 		= [];
	
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function run()
	{
		// kill all NULL
		$locations = LocationModule::find(['city is NULL']);
		foreach ($locations as $id => $loc) {
			$loc -> delete();
		}
		
		$query = new \Phalcon\Mvc\Model\Query("SELECT distinct(city) as city, id as id FROM Models\Location group by city order by city limit 230", $this -> di);
		$locations = $query -> execute();
		
		foreach ($locations as $loc) {
			$items = LocationModule::find(['city = "' . $loc -> city . '"']);
// var_dump($items -> toArray());
// die();
			foreach ($items as $loc) {
				$locHash = hash('md5', trim($loc -> city) . trim($loc -> state) . trim($loc -> country));
				$this -> locScope[$locHash][] = $loc;							
			}

			foreach ($this -> locScope as $hash => $scope) {
				$baseLocation = $this -> getMaxLocation($scope);
print_r("\n\r");				
print_r($baseLocation -> id . ' : ' . $baseLocation -> city . ' : ' . $baseLocation -> state . ' : ' .  $baseLocation -> country);
						
				foreach ($scope as $sc) {
					if ($sc -> id != $baseLocation -> id) {
						$this -> transferEventsToMaxLocation($baseLocation -> id, $sc);
						$sc -> delete();
					}
				}
				$baseLocation -> search_alias = strtolower(preg_replace("/['\s]+/", '-', $baseLocation -> city));

				if ($apiResult = $this -> di -> get('geo') -> makeRequest($baseLocation -> city, $baseLocation -> state, $baseLocation -> country)) {
					$baseLocation -> latitudeMax = number_format($apiResult[0] -> geometry -> viewport -> northeast -> lat, 8);
					$baseLocation -> latitudeMin = number_format($apiResult[0] -> geometry -> viewport -> southwest -> lat, 8);
					$baseLocation -> longitudeMax = number_format($apiResult[0] -> geometry -> viewport -> northeast -> lng, 8);
					$baseLocation -> longitudeMin = number_format($apiResult[0] -> geometry -> viewport -> southwest -> lng, 8);
//var_dump($baseLocation -> toArray()); die();					
					$baseLocation -> update();
				} else {
					$baseLocation -> delete();
				}
			}
		}
print_r("\n\rdone\n\r");		
die();	
	}
	
	
	protected function getMaxLocation($scope)
	{
		$maxSc = $scope[0];
		$maxCount = 0;
		
		foreach ($scope as $sc) {
			$scCount = (new Event()) -> getNumByCriteria($sc -> id);
			if ($scCount > $maxCount) {
				$maxCount  = $scCount;
				$maxSc = $sc;
			}
		}

		return $maxSc;
	}
	
	
	protected function transferEventsToMaxLocation($baseLocation, $scope)
	{
		$events = (new \Models\Event()) -> setShardByCriteria($scope -> id);
		$eToMove = $events -> strictSqlQuery()
						   -> addQueryCondition('location_id = ' . $scope -> id)
						   -> addQueryFetchStyle('\Models\Event')
						   -> selectRecords();
		
		if ($eToMove) {
			foreach ($eToMove as $eventObj) {
				$newEventObj = (new Event()) -> transferEventBetweenShards($eventObj, $baseLocation -> id);
			}
		}	
		
		return;
	}
}