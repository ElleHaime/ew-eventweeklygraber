<?php

namespace Jobs\Cache;

class Fbeventuids
{
    public $cacheData;
    private $prefix;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
	}
	
	public function run($keyPrefix = false)
	{
		if ($keyPrefix) {
			$this -> prefix = $keyPrefix . '_';
		}
		
		$e = new \Models\Event();
		$fbUids = 0;		
		$availableShards = $e -> getAvailableShards();
		
		foreach ($availableShards as $index => $shard) {
			$e -> setShard($shard);
			$events = $e::find(['fb_uid is not null']);
			$fbUids += $events -> count();
			 
			foreach ($events as $uid) {
				$this -> cacheData -> save($this -> prefix . $uid -> fb_uid, $uid -> fb_uid);
			} 
		}
		
		print_r("Facebook uids cached.\n\rTotal " . $fbUids . "\n\r");
		exit();  
	}
}