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
				|| ($taskCustom -> state == Cron::STATE_INTERRUPTED && (time() - $taskCustom -> hash) > 3600)
				|| ($taskCustom -> state == Cron::STATE_EXECUTED && (time() - $taskCustom -> hash) > 86400)) 
			{
				$maxTime = time() - 1300;
				$taskUser = Cron::findFirst(['name  = "' . Cron::FB_TASK_NAME . '" AND hash > ' . $maxTime,
										 	 'order' => 'id DESC']);
				
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
			
			sleep(60);
		}
	}
	
	
	public function observedataAction() 
	{
		while (true) {
			$tasks = Cron::find('state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_HANDLING . ') AND name  = "' . Cron::FB_BY_ID_TASK_NAME . '"');
		
			if ($tasks) {
				foreach ($tasks as $task) {
					$args = unserialize($task -> parameters);
			        $task -> state = Cron::STATE_HANDLING;
			        $task -> update();
			        
	        		$this -> console -> handle(['task' => 'Tasks\Facebook\Custom\Grab',
						        				'action' => 'harvestdata',
						        				'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id'], $task -> id]]);
				}
			} 
			sleep(1);
		}
	}

	public function testrabbitAction()
	{
		$this -> console -> handle(['task' => 'harvester', 
									'action' => 'test',
									'params' => []]);
	}

	public function testAction(array $args)
	{
		echo sprintf('Token is %s', $args[0]) . PHP_EOL;
		echo sprintf('AccId is %s', $args[1]) . PHP_EOL;
		echo "\n\n";
	}

}