<?php

namespace Jobs\Grabber\Cacher;

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