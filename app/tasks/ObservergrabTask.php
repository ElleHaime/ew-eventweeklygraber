<?php

namespace Tasks;

use \Models\Cron,
	\Tasks\HarvesterTask,
	\Tasks\HarvestgraphTask;

class observergrabTask extends \Phalcon\CLI\Task
{
	const FB_TASK_NAME = 'extract_facebook_events';
	const FB_GRAPH_TASK_NAME = 'extract_graph_facebook_events';
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
	
	
	public function observegraphAction()
	{
		$harvestTask = new HarvestgraphTask();
		
		while (true) {
			$tasks = Cron::find('state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_HANDLING . ') AND name  = "' . self::FB_TASK_NAME . '"');
			if ($tasks) {
				foreach ($tasks as $task) {
					$args = unserialize($task -> parameters);
			        $task -> state = Cron::STATE_HANDLING;
			        //$task -> update();
			        
	        		$this -> console -> handle(['task' => 'harvestgraph',
						        				'action' => 'harvest',
						        				'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id'], $task -> id]]);
				}
			} 
			sleep(1);
		}
	}
	
/*	
	public function observeAction() {
		while (true) {
			$tasks = Cron::find(['state = ' . Cron::STATE_PENDING, 'name in("' . self::FB_TASK_NAME . '", "' . self::CACHE_TASK_NAME . '")']);
			if ($tasks) {
				foreach ($tasks as $task) {
					$args = unserialize($task -> parameters);
					$task -> state = Cron::STATE_HANDLING;
					$task -> update();
					 
					switch ($task -> name) {
						case self::FB_TASK_NAME:
							$this -> console -> handle(['task' => 'harvester',
							'action' => 'harvest',
							'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id']]]);
							break;
						case self::CACHE_TASK_NAME:
							$this -> console -> handle(['task' => 'cachergrab',
							'action' => 'counters',
							'params' => [$args['member_id']]]);
							break;
					}
				}
			}
			sleep(2);
		}
	}
*/
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