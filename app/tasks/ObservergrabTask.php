<?php

namespace Tasks;

use \Models\Cron,
	\Tasks\HarvesterTask,
	\Tasks\HarvestgraphTask;

class observergrabTask extends \Phalcon\CLI\Task
{
	const FB_TASK_NAME = 'extract_facebook_events';
	const FB_CREATORS_TASK_NAME = 'extract_creators_facebook_events';
	const CACHE_TASK_NAME = 'cache_events_counters';	

	public function observeAction() {
		$harvestTask = new HarvesterTask();
		
		while (true) {
			$tasks = Cron::find('state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_HANDLING . ') AND name  = "' . self::FB_TASK_NAME . '"');
			if ($tasks) {
				foreach ($tasks as $task) {
					$args = unserialize($task -> parameters);
			        $task -> state = Cron::STATE_HANDLING;
			        $task -> update();
			        
	        		$this -> console -> handle(['task' => 'harvester',
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