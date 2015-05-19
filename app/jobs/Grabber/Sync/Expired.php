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
			
			$expired = $events::find(['end_date < "' . date('Y-m-d H:i:s') . '"']);
			if ($expired -> count() > 0) {
				foreach ($expired as $eventObj) {
					$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
					$indexer = new \Models\Event\Search\Indexer($grid);
					$indexer -> setDi($this -> di);
					if (!$indexer -> deleteData($eventObj -> id)) {
						print_r("ooooooops, " . $eventObj -> id . " not removed from index\n\r");
					}
					
					$event -> archive(); 
				}
			}
		}
	}
}