<?php

namespace Jobs\Application\Cacher;

class Counters
{
    public $cacheData;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
	}
	
	public function run($userId)
	{
		$model = new \Models\EventLike();
		$elSummary = $model -> getLikedEventsCount($userId);
		$this -> processCounters($elSummary, 'member.like.' . $userId . '.', 'userEventsLiked.' . $userId);

		print_r("Data cached: liked events for member #" . $userId . "\n\r");		
	}
	
	private function processCounters($data, $cacheNameItem, $cacheNameSum)
	{
		foreach ($data as $item) {
			if (!$this -> cacheData -> exists($cacheNameItem . $item -> id)) {
				$this -> cacheData -> save($cacheNameItem . $item -> id, $item -> fb_uid);
			}
		}
	}
}