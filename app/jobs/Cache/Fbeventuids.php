<?php

namespace Jobs\Cache;

class Fbeventuids
{
    public $cacheData;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
	}
	
	public function run()
	{
		$e = new \Models\Event();
		$availableShards = $e -> getAvailableShards();
		
		foreach ($availableShards as $index => $shard) {
			$e -> setShard($shard);
			$events = $e::find(['fb_uid is not null']);
		
			foreach ($events as $uid) {
				$this -> cacheData -> save($uid -> fb_uid, $uid_fb_uid);
			}
		}
		
		print_r("facebook uids cache is full\n\r\");
	}
}