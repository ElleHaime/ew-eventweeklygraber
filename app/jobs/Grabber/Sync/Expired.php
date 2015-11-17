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
		$finishDate = date('Y-m-d H:i:s');
		
		foreach ($shards as $cri) {
			$events = (new \Models\Event()) -> setShard($cri);
			$expiredTotal = $events -> strictSqlQuery()
									-> addQueryCondition('end_date < "' . $finishDate . '"')
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
print_r("batch: " . $expCount . " events\n\r");
					if ($expired) {
						foreach ($expired as $eventObj) {
							print_r(".");
							$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> di, null, ['adapter' => 'dbMaster']);
							$indexer = new \Models\Event\Search\Indexer($grid);
							$indexer -> setDi($this -> di);
							$indexer -> deleteData($eventObj -> id);

							$eventObj -> archivePhalc();
						}
						//$offset += $this -> batchSize;
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
}