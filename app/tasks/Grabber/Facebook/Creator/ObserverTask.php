<?php

namespace Tasks\Facebook\Creator;

use \Models\Cron;

class ObserverTask extends \Phalcon\CLI\Task
{
	const FB_TASK_NAME = 'extract_facebook_events';
	const FB_CREATORS_TASK_NAME = 'extract_creators_facebook_events';

	public function observeAction()
	{
		while (true) {
			$task = Cron::findFirst(['name = "' . self::FB_CREATORS_TASK_NAME . '" 
												AND state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_EXECUTED . ', ' . Cron::STATE_INTERRUPTED . ')',
											'order' => 'id DESC']);
			if (!$task
				|| $task -> state == Cron::STATE_PENDING
				|| ($task -> state == Cron::STATE_INTERRUPTED && (time() - $task -> hash) > 3600)
				|| ($task -> state == Cron::STATE_EXECUTED && (time() - $task -> hash) > 86400))
			{
				$maxTime = time() - 1300;
				$taskUser = Cron::findFirst(['name  = "' . self::FB_TASK_NAME . '" AND hash > ' . $maxTime,
											 'order' => 'id DESC']);
				if ($taskUser) {
					if (!$task || $task -> state == Cron::STATE_EXECUTED) {
						$task = new Cron;
						$task -> name = self::FB_CREATORS_TASK_NAME;
					}
					$task -> member_id = $taskUser -> member_id;
					$task -> parameters = $taskUser -> parameters;
					$task -> state = Cron::STATE_HANDLING;
					$task -> hash = time();
					
					!$task -> id ? $task -> save() : $task -> update(); 
					
					$args = unserialize($task -> parameters);
					$this -> console -> handle(['task' => 'Tasks\Facebook\Creator\Grab',
						        				'action' => 'harvest',
						        				'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id'], $task -> id]]);					
				}
			}
			
			sleep(60);
		}
	}
}