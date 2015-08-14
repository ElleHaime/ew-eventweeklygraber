<?php

namespace Jobs\Grabber\Sync;

class Expired
{
	public $di;
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function run()
	{
		$shards = (new \Models\Event()) -> getAvailableShards();
		
		foreach ($shards as $cri) {
			$events = (new \Models\Event()) -> setShard($cri);
			$query = 'select * from Event where Models\Event.end_date < "' . date('Y-m-d H:i:s') . '"';
			//$expired = $events -> getModelsManager() -> executeQuery($phql, [$endDate]);
			
			$expired = $events -> strictSqlQuery()
							   -> addQueryCondition('end_date < "' . date('Y-m-d H:i:s') . '"')
							   -> addQueryFetchStyle('\Models\Event')
							   -> select();
print_r(count($expired) . "\n\r");			
			if ($expired) {
				foreach ($expired as $eventObj) {
print_r(".");					
					$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
					$indexer = new \Models\Event\Search\Indexer($grid);
					$indexer -> setDi($this -> di);
					if (!$indexer -> deleteData($eventObj -> id)) {
						print_r("\n\rooooooops, " . $eventObj -> id . " not removed from index\n\r");
					} 
						
					$eventObj -> archive(); 
				}
			}
print_r("\n\r\n\r");			
		}
	}
}