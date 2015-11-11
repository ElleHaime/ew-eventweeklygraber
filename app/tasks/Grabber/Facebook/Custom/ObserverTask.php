<?php

namespace Tasks\Facebook\Custom;

use \Models\Cron;

class ObserverTask extends \Phalcon\CLI\Task
{
	public function observeidAction() 
	{
		while (true) {
			$taskCustom = Cron::findFirst(['name  = "' . Cron::FB_GET_ID_TASK_NAME . '" 
												AND state IN (' . Cron::STATE_EXECUTED . ', ' . Cron::STATE_INTERRUPTED . ', ' . Cron::STATE_PENDING . ')',
									 	   'order' => 'id DESC']);
			
			if (!$taskCustom 
				|| $taskCustom -> state == Cron::STATE_PENDING
				|| ($taskCustom -> state == Cron::STATE_INTERRUPTED && (time() - $taskCustom -> hash) > Cron::SLEEP_INTERRUPTED)
				|| ($taskCustom -> state == Cron::STATE_EXECUTED && (time() - $taskCustom -> hash) > Cron::SLEEP_EXECUTED)) 
			{
				$maxTime = time() - 1300;
				$taskUser = Cron::findFirst(['name  = "' . Cron::FB_TASK_NAME . '" AND hash > ' . $maxTime, 'order' => 'id DESC']);
				
				if ($taskUser) {
						if (!$taskCustom || $taskCustom -> state == Cron::STATE_EXECUTED) {
							// create new custom task
							$taskCustom = new Cron;
							$taskCustom -> name = Cron::FB_GET_ID_TASK_NAME;
						} 
						// assign to custom task recent parameters from user task
						$taskCustom -> member_id = $taskUser -> member_id;
						$taskCustom -> parameters = $taskUser -> parameters;
						$taskCustom -> state = Cron::STATE_HANDLING;
						$taskCustom -> hash = time();
						
						if (!$taskCustom -> id) {
							$taskCustom -> save();
						} else {
							$taskCustom -> update();
						}
						
						$args = unserialize($taskCustom -> parameters);
						$this -> console -> handle(['task' => 'Tasks\Facebook\Custom\Grab',
							        				'action' => 'harvestid',
							        				'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id'], $taskCustom -> id]]);						
				}
			}
			
			sleep(Cron::SLEEP_PAUSE);
		}
	}
	
	
	public function observedataAction() 
	{
		while (true) {
			$taskCustom = Cron::findFirst('state IN (' . Cron::STATE_INTERRUPTED . ', ' . Cron::STATE_PENDING . ') AND name  = "' . Cron::FB_BY_ID_TASK_NAME . '"');
			
			if ($taskCustom && ($taskCustom -> state == Cron::STATE_PENDING
				|| ($taskCustom -> state == Cron::STATE_INTERRUPTED && (time() - $taskCustom -> hash) > Cron::SLEEP_INTERRUPTED)
				|| ($taskCustom -> state == Cron::STATE_EXECUTED && (time() - $taskCustom -> hash) > Cron::SLEEP_EXECUTED))) 
			{
				$maxTime = time() - 1300;
				$taskUser = Cron::findFirst(['name  = "' . Cron::FB_TASK_NAME . '" AND hash > ' . $maxTime, 'order' => 'id DESC']);
				
				if ($taskUser) {
					$taskCustom -> member_id = $taskUser -> member_id;
					$taskCustom -> parameters = $taskUser -> parameters;
					$taskCustom -> state = Cron::STATE_HANDLING;
					$taskCustom -> hash = time();
					$taskCustom -> update();
					
					$args = unserialize($taskCustom -> parameters);
			        
	        		$this -> console -> handle(['task' => 'Tasks\Facebook\Custom\Grab',
						        				'action' => 'harvestdata',
						        				'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id'], $taskCustom -> id]]);
				}
			} 
			sleep(Cron::SLEEP_PAUSE);
		}
	}
}