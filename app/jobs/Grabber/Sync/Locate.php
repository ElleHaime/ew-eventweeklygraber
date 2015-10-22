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

		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
			$events = (new \Models\Event()) -> setShard($cri);
			$expiredTotal = $events -> strictSqlQuery()
									-> addQueryCondition('location_id  "' . $finishDate . '"')
									-> selectCount();
$mem_usage = memory_get_usage();
echo "\nUse memory ".round($mem_usage/1048576,2)." megabytes";
print_r("\n\r" . $events -> getShardTable() . ": " . $expiredTotal . " events\n\r");
			
			if ($expiredTotal > 0) {
				$offset = 0; 
				do {
					$expired = $events -> strictSqlQuery()
									   -> addQueryCondition('end_date < "' . $finishDate . '"')
									   -> addQueryFetchStyle('\Models\Event')
									   -> addQueryLimits($this -> batchSize, $offset)
									   -> selectRecords();
					$expCount = count($expired);
					if ($expired) {
						foreach ($expired as $eventObj) {
							print_r(".");
							$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
							$indexer = new \Models\Event\Search\Indexer($grid);
							$indexer -> setDi($this -> di);
							$indexer -> deleteData($eventObj -> id);

							$eventObj -> archivePhalc();
						}
						$offset += $this -> batchSize;
					}
$mem_usage = memory_get_usage();
echo "\n\rUse memory ".round($mem_usage/1048576,2)." megabytes\n";
 				} while ($expCount >= $this -> batchSize);
$mem_usage = memory_get_usage();
echo "\n\rUse memory ".round($mem_usage/1048576,2)." megabytes\n";
				
print_r("\n\r");
			}
	}
}