<?php

namespace Jobs\Grabber\Sync;

class Expired
{
	public $di;
	protected $batchSize = 500;
	
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function run()
	{
		$shards = (new \Models\Event()) -> getAvailableShards();
		
		foreach ($shards as $cri) {
			$events = (new \Models\Event()) -> setShard($cri);
			$expiredTotal = $events -> strictSqlQuery()
									-> addQueryCondition('end_date < "' . date('Y-m-d H:i:s') . '"')
									-> selectCount();
			
			if ($expiredTotal > 0) {
				$offset = 0; 
				do {
					$expired = $events -> strictSqlQuery()
									   -> addQueryCondition('end_date < "' . date('Y-m-d H:i:s') . '"')
									   -> addQueryFetchStyle('\Models\Event')
									   -> addQueryLimits($this -> batchSize, $offset)
									   -> selectRecords();
					$expCount = count($expired);
print_r($events -> getShardTable() . ": " . $expCount . " events\n\r");
					if ($expired) {
						foreach ($expired as $eventObj) {
							print_r(".");
							$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
							$indexer = new \Models\Event\Search\Indexer($grid);
							$indexer -> setDi($this -> di);
							$indexer -> deleteData($eventObj -> id);
							$eventObj -> archive();
						}
						$offset += $this -> batchSize;
					}
				} while ($expCount >= $this -> batchSize);
print_r("\n\r");
			}
print_r("\n\r\n\r");			
		}
		
		
// 		foreach ($shards as $cri) {
// 			$events = (new \Models\Event()) -> setShard($cri);
// 			$query = 'select * from Event where Models\Event.end_date < "' . date('Y-m-d H:i:s') . '"';
			
// 			$expired = $events -> strictSqlQuery()
// 							   -> addQueryCondition('end_date < "' . date('Y-m-d H:i:s') . '"')
// 							   -> addQueryFetchStyle('\Models\Event')
// 							   -> select();
// print_r(count($expired) . "\n\r");			
// 			if ($expired) {
// 				foreach ($expired as $eventObj) {
// print_r(".");					
// 					$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
// 					$indexer = new \Models\Event\Search\Indexer($grid);
// 					$indexer -> setDi($this -> di);
// 					$indexer -> deleteData($eventObj -> id);
// 					$eventObj -> archive(); 
// 				}
// 			}
// print_r("\n\r\n\r");			
// 		}
	}
}