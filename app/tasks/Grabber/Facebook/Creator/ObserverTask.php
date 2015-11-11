<?php

namespace Tasks\Facebook\Creator;

use \Models\Cron;

class ObserverTask extends \Phalcon\CLI\Task
{
	public function observeVenueAction()
	{
		while (true) {
			$task = Cron::findFirst(['name = "' . Cron::FB_CREATOR_TASK_NAME . '"
											AND state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_EXECUTED . ', ' . Cron::STATE_INTERRUPTED . ')',
											'order' => 'id DESC']);
			if (!$task
					|| $task -> state == Cron::STATE_PENDING
					|| ($task -> state == Cron::STATE_INTERRUPTED && (time() - $task -> hash) > Cron::SLEEP_INTERRUPTED)
					|| ($task -> state == Cron::STATE_EXECUTED && (time() - $task -> hash) > Cron::SLEEP_EXECUTED))
			{
				$maxTime = time() - 300;
				$taskUser = Cron::findFirst(['name  = "' . Cron::FB_TASK_NAME . '" AND hash > ' . $maxTime,
				 							'order' => 'id DESC']);
		
				if ($taskUser) {
					if (!$task || $task -> state == Cron::STATE_EXECUTED) {
						$task = new Cron;
						$task -> name = Cron::FB_CREATOR_TASK_NAME;
					}
					$task -> member_id = $taskUser -> member_id;
					$task -> parameters = $taskUser -> parameters;
					$task -> state = Cron::STATE_HANDLING;
					$task -> hash = time();
						
					!$task -> id ? $task -> save() : $task -> update(); 
						
					$args = unserialize($task -> parameters);
					$this -> console -> handle(['task' => 'Tasks\Facebook\Creator\Grab',
													'action' => 'harvestVenue',
													'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id'], $task -> id]]);
				}
				
				sleep(Cron::SLEEP_PAUSE);
			}
		}
	}
}