<?php

namespace Jobs\Grabber\Sync;

use Models\Location;


class Locate
{
	public $di;
	protected $batchSize = 200;
	
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function runEvents()
	{
		$model = (new \Models\Event()) -> setShardByCriteria(0);
		$events = $model -> strictSqlQuery()
						 -> addQueryCondition('location_id=0')
						 -> addQueryFetchStyle('\Models\Event')
						 -> selectRecords();
		
		if ($events) {
			foreach ($events as $eventObj) {
				if (!empty($eventObj -> latitude) && !empty($eventObj -> longitude)) {
					$eventLocation = (new Location()) -> createOnChange(['latitude' => $eventObj -> latitude, 'longitude' => $eventObj -> longitude]);
				}
			}
		}
	}
}