<?php

namespace Jobs\Grabber\Sync;

use Models\Location as LocationModule;


class Location
{
	public $di;
	public $locationsCleanedIds = [];
	
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function run()
	{
		$query = new \Phalcon\Mvc\Model\Query("SELECT distinct(city) FROM Models\Location group by city order by id", $this -> di);
		$locations = $query -> execute();
var_dump($locations -> toArray()); die();
		foreach ($locations as $loc) {
			$items = LocationModule::find(['city = "' . $loc -> toArray()[0] . '"']);
			if ($items -> count() > 1) {
				
			}
		}
		
		
// 		//$locations = LocationModule::find(['distinct city']);
// 		//$locationsCleaned = $locations;
// 		$query = new \Phalcon\Mvc\Model\Query("SELECT distinct(city) FROM Models\Location group by city order by city", $this -> di);
// 		$locations = $query -> execute();
// var_dump($locations -> count());
// die();
// 		foreach ($locations as $loc) {
// 			var_dump($loc -> city . ' ' . $loc -> state);
// 			if (!isset($locationsCleanedIds[$loc -> id])) {
// 				// add search alias: city with replaced spaces and aps 
// 				$loc -> search_alias = strtolower(preg_replace("/['\s]+/", '-', $loc -> city));
	
// //				$loc -> update();
// 				$ids = $locationsCleaned -> filter(
// 						function($location) use ($loc) {
// 							if ($location -> city == $loc -> city && $location -> country == $loc -> country
// 									&& $location -> state == $loc -> state && $location -> id != $loc -> id) 
// 							{
// 								$this -> locationsCleanedIds[$location -> id] = 1;
// 							}	
// 							return $this -> locationsCleanedIds;
// 						});
// 			}
// 		}
// var_dump($this -> locationsCleanedIds);
// die();
	}
}