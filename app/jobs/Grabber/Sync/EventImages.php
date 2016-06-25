<?php

namespace Jobs\Grabber\Sync;

class EventImages
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
			$e = (new \Models\Event()) -> setShard($cri);
			$events = $e -> strictSqlQuery()
						 -> selectRecords();
			
			foreach ($events as $ev) {
				$sourceDir = $this -> di -> get('config') -> application -> uploadDir -> event . $ev['id'];
				if (is_dir($sourceDir)) {
					if (is_null($ev['start_date'])) {
						$destDir = $this -> di -> get('config') -> application -> uploadDir -> eventReserveDir . 'undated/' . $ev['id'];
					} else {
						$destDir = $this -> di -> get('config') -> application -> uploadDir -> eventReserveDir 
							. date('Y', strtotime($ev['start_date'])) . '/'
							. date('m', strtotime($ev['start_date'])) . '/'
							. date('d', strtotime($ev['start_date'])) . '/'
							. $ev['id'];
					}
// 					var_dump($sourceDir);
// 					var_dump($destDir);
					$this -> copyImagesRecursively($sourceDir, $destDir);
// 					print_r("\n\r\n\r");
				} 
			}
		}
die("done");		
	}
	
	
	private function copyImagesRecursively($source, $dest)
	{
		if (!is_dir($dest)) mkdir($dest, 0777, true); 
			
		foreach ($iterator = new \RecursiveIteratorIterator(
						new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
						\RecursiveIteratorIterator::SELF_FIRST) as $item)
		{
			if($item -> isDir()) {
				mkdir($dest . DIRECTORY_SEPARATOR . $iterator -> getSubPathName());
			} else {
				copy($item, $dest . DIRECTORY_SEPARATOR . $iterator -> getSubPathName());
			}
		}
		
		return;
	}
}