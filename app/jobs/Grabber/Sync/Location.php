<?php

namespace Jobs\Grabber\Sync;

use Models\Location as LocationModule,
	Models\Event,
	Library\Utils\SlugUri as _Slug;


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
// print_r("\n\rdone\n\r\n\r");
// die();		
		//$query = new \Phalcon\Mvc\Model\Query("SELECT distinct(city) as city, id as id FROM Models\Location where place_id not like 'Ch%' and city > 'Dubbo' group by city order by city limit 1", $this -> di);
		$query = new \Phalcon\Mvc\Model\Query("SELECT distinct(city) as city, id as id FROM Models\Location where place_id not like 'Ch%' group by city order by id limit 100", $this -> di);
		$locations = $query -> execute();
		
		foreach ($locations as $loc) {
print_r("\n\r\n\r\n\r" . $loc -> city);
			$model = new LocationModule();
			$items = new \Phalcon\Mvc\Model\Resultset\Simple(null, $model, 
						$model -> getReadConnection() -> query("SELECT * FROM location WHERE city = x'" . bin2hex($loc -> city). "'"));

print_r(" : " . $items -> count());
				
			foreach ($items as $loc) {
				$locHash = hash('md5', trim($loc -> city) . trim($loc -> state) . trim($loc -> country));
				$this -> locScope[$locHash][] = $loc;							
			}

			foreach ($this -> locScope as $hash => $scope) {
				$baseLocation = $this -> getMaxLocation($scope);
print_r("\n\r" . $baseLocation -> id . ' : ' . $baseLocation -> city . ' : ' . $baseLocation -> state . ' : ' .  $baseLocation -> country);

				foreach ($scope as $sc) {
					if ($sc -> id != $baseLocation -> id) {
print_r("\n\r" . $sc -> id );
						$this -> transferEventsToMaxLocation($baseLocation -> id, $sc);
						$sc -> delete();
					}
				}
	
				if ($apiResult = $this -> di -> get('geo') -> makeRequest($baseLocation -> city, $baseLocation -> state, $baseLocation -> country)) {
					$baseLocation -> latitudeMax = number_format($apiResult['geometry'] -> northeast -> lat, 8);
					$baseLocation -> latitudeMin = number_format($apiResult['geometry'] -> southwest -> lat, 8);
					$baseLocation -> longitudeMax = number_format($apiResult['geometry'] -> northeast -> lng, 8);
					$baseLocation -> longitudeMin = number_format($apiResult['geometry'] -> southwest -> lng, 8);
					$baseLocation -> city = $apiResult['locality'] -> long_name;
					$baseLocation -> alias = $apiResult['locality'] -> long_name;
					$baseLocation -> place_id = $apiResult['place_id'];
					$baseLocation -> search_alias = _Slug::slug($baseLocation -> city);

var_dump($baseLocation -> toArray()); 
					$baseLocation -> update();
print_r("\n\rupdated with id " . $baseLocation -> id); //die();					
				} else {
print_r("\n\rdeleted with id " . $baseLocation -> id); //die();
					$baseLocation -> delete();
				}
			}
				
			$this -> locScope = [];
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
				$newEventObj = (new Event()) -> transferEventBetweenShards($eventObj, $baseLocation);
			}
		}	
		
		return;
	}
}