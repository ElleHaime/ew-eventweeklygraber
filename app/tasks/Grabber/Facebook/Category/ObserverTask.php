<?php

namespace Tasks\Facebook\Category;

use \Models\Cron;

class ObserverTask extends \Phalcon\CLI\Task
{
	public function observeAction() 
	{
		$taskUser = Cron::findFirst(['name  = "' . Cron::FB_TASK_NAME . '"', 'order' => 'id DESC']);		
		
		if ($taskUser) {
			$args = unserialize($taskUser -> parameters);
			$this -> console -> handle(['task' => 'Tasks\Facebook\Category\Grab',
					'action' => 'listen',
					'params' => [$args['user_token']]]);
		}
		die();
	}
}