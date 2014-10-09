<?php

namespace Tasks\Facebook\User;

use \Models\Cron,
	\Tasks\Facebook\User\Grab;

class ObserverTask extends \Phalcon\CLI\Task
{
	const FB_TASK_NAME = 'extract_facebook_events';

	public function observeAction() 
	{
		while (true) {
			$tasks = Cron::find('state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_HANDLING . ') AND name  = "' . self::FB_TASK_NAME . '"');
			if ($tasks) {
				foreach ($tasks as $task) {
					$args = unserialize($task -> parameters);
			        $task -> state = Cron::STATE_HANDLING;
			        $task -> update();
			        
	        		$this -> console -> handle(['task' => 'Tasks\Facebook\User\Grab',
						        				'action' => 'harvest',
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