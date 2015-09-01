<?php

namespace Jobs\Grabber\Clean;

class Events
{
	public $di;
	protected $batchSize = 200;
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function run()
	{
		$shards = (new \Models\Event()) -> getAvailableShards();
		
		foreach ($shards as $cri) {
			$events = (new \Models\Event()) -> setShard($cri);
			$eventsTotal = $events -> strictSqlQuery()
									-> addQueryCondition('fb_uid is not null')
									-> selectCount();
			if ($eventsTotal > 0) {
				$offset = 0;
				$stack = [];
				
				do {
					$items = $events -> strictSqlQuery()
									  -> addQueryCondition('fb_uid is not null')
									  -> addQueryFetchStyle('\Models\Event')
									  -> addQueryLimits($this -> batchSize, $offset)
									  -> selectRecords();
					$itemsCount = count($items);

					
					if (!empty($items)) {
						$drop = [];
						
						foreach ($items as $eventObj) {
							!isset($stack[$eventObj -> fb_uid]) 
									? $stack[$eventObj -> fb_uid] = $eventObj -> id
									: $drop[$eventObj -> id] = $eventObj;
						}
print_r(count($stack) . " : " . count($drop) . "\n\r");						
						if (!empty($drop)) {
							foreach ($drop as $key => $object) {
								print_r("\n\r" . $key);
								
								$grid = new \Models\Event\Grid\Search\Event(['location' => $object -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
								$indexer = new \Models\Event\Search\Indexer($grid);
								$indexer -> setDi($this -> di);
								$indexer -> deleteData($object -> id);
								
								$object -> archivePhalc(false);
							}
						}
						$offset += $this -> batchSize;
					}
				} while ($itemsCount >= $this -> batchSize);
				
				print_r("\n\r");
			}
		}
	}
}