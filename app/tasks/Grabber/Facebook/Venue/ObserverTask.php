<?php

namespace Tasks\Facebook\Venue;

use \Models\Cron,
	\Models\Venue,
	\Queue\Producer\Producer;

class ObserverTask extends \Phalcon\CLI\Task
{
	public function observeAction() 
	{
// 		$task = Cron::findFirst(['name  = "' . Cron::FB_VENUE_TASK_NAME . '"
// 										AND state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_EXECUTED . ', ' . Cron::STATE_INTERRUPTED . ')', 
// 									'order' => 'id DESC']);		
		
// 		if (!$task
// 				|| $task -> state == Cron::STATE_PENDING
// 				|| ($task -> state == Cron::STATE_INTERRUPTED && (time() - $task -> hash) > Cron::SLEEP_INTERRUPTED)
// 				|| ($task -> state == Cron::STATE_EXECUTED && (time() - $task -> hash) > Cron::SLEEP_EXECUTED))
// 		{
// 			$maxTime = time() - 1000;
// 			$taskUser = Cron::findFirst(['name  = "' . Cron::FB_TASK_NAME . '" AND hash > ' . $maxTime,
// 										 'order' => 'id DESC']);
		
// 			if ($taskUser) {
// 				if (!$task || $task -> state == Cron::STATE_EXECUTED) {
// 					$task = new Cron;
// 					$task -> name = Cron::FB_CREATOR_TASK_NAME;
// 				}
// 				$task -> member_id = $taskUser -> member_id;
// 				$task -> parameters = $taskUser -> parameters;
// 				$task -> state = Cron::STATE_HANDLING;
// 				$task -> hash = time();
		
// 				!$task -> id ? $task -> save() : $task -> update();
		
// 				$args = unserialize($task -> parameters);
// 				if ($args['source'] == 'tables') {
// 					$this -> initQueue('harvesterVenues');
					
// 					$venues = Venue::find(['fb_uid is not null']);
// 					foreach ($venues as $venueObj) {
// 						$this -> queue -> publish(serialize([$venueObj -> id => $venueObj -> fb_uid]));
// 					}
// 				}

				$this -> console -> handle(['task' => 'Tasks\Facebook\Venue\Grab',
											'action' => 'listenTables']);
// 											'params' => [$args['user_token'], $task -> id]]);
// 			}
		
// 			sleep(Cron::SLEEP_PAUSE);
// 		}
		die();
	}
}