<?php

namespace Jobs\Grabber\Categorize;


class Categorize
{
	use \Jobs\Grabber\Parser\Helper;
	
	public $di;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}


	public function run()
	{
		$shards = (new \Models\Event()) -> getAvailableShards();

		foreach ($shards as $cri) {
			$shard = (new \Models\Event()) -> setShard($cri);
			$limit = 100;
			$offset = 0;
			$count = 0;
			
			while($count == $limit) {
				$events = $shard::find(['limit' => ['number' => $limit, 'offset' => $offset]]);
				$count = $events -> count();
				
				foreach($events as $eventObj) {
					$this -> categorize($eventObj);
					
					$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
					$indexer = new \Models\Event\Search\Indexer($grid);
					$indexer -> setDi($this -> di);
					if ($indexer -> existsData($eventObj -> id)) {
						$indexer -> updateData($eventObj -> id);
					} else {
						$indexer -> addData($eventObj -> id);
					}
				}
			}
		}
		
		return;
	}
}