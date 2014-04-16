<?php

namespace Jobs\Cacher;

class Counters
{
    public $cacheData;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
	}
	
	public function run($userId)
	{
		$model = new \Models\Event();
		$ecSummary = $model -> getCreatedEventsCount($userId);
		$this -> processCounters($ecSummary, 'member.create.' . $userId . '.', 'userEventsCreated.' . $userId);
		
		$model = new \Models\EventLike();
		$elSummary = $model -> getLikedEventsCount($userId);
		$this -> processCounters($elSummary, 'member.like.' . $userId . '.', 'userEventsLiked.' . $userId);
		
		$model = new \Models\EventMember();
		$emSummary = $model -> getEventMemberEventsCount($userId);
		$this -> processCounters($emSummary, 'member.go.' . $userId . '.', 'userEventsGoing.' . $userId);
		
		$model = new \Models\EventMemberFriend();
		$emfSummary = $model -> getEventMemberFriendEventsCount($userId);
		$this -> processCounters($emfSummary, 'member.friends.go.' . $userId . '.', 'userFriendsGoing.' . $userId);
		
		print_r("Data cached: member #" . $userId . "\n\r");		
	}
	
	private function processCounters($data, $cacheNameItem, $cacheNameSum)
	{
		/*if (!$this -> subject -> cacheData -> exists($cacheNameSum)) {
			$this -> subject -> cacheData -> save($cacheNameSum, 0);
		}*/

		foreach ($data as $item) {
			if (!$this -> cacheData -> exists($cacheNameItem . $item -> id)) {
				$this -> cacheData -> save($cacheNameItem . $item -> id, $item -> fb_uid);
	
			//	$this -> subject -> cacheData -> save($cacheNameSum, $this -> subject -> cacheData -> get($cacheNameSum)+1);
			}
		}
	}
}