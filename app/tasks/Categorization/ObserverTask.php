<?php

namespace Tasks\Categorization;

use \Models\Cron;

class ObserverTask extends \Phalcon\CLI\Task
{
	public function observeAction() 
	{
		$tasks = Cron::find('state IN (' . Cron::STATE_PENDING . ', ' . Cron::STATE_HANDLING . ') AND name  = "' . Cron::RECAT_TASK_NAME . '"');

		if ($tasks) {
			foreach ($tasks as $task) {
		        $task -> state = Cron::STATE_HANDLING;
		        $task -> update();
		        
        		$this -> console -> handle(['task' => 'Tasks\Categorization\Categorize',
					        				'action' => 'recategorize',
					        				'params' => [$task -> id]]);
        		$task -> state = Cron::STATE_EXECUTED;
		        $task -> update();
			}
		} 
	}
}