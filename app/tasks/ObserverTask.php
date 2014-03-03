<?php

namespace Tasks;

use \Models\Cron;

class observerTask extends \Phalcon\CLI\Task
{
	const FB_TASK_NAME = 'extract_facebook_events';


	public function observeAction() {

		$cron = new Cron();
		$tasks = $cron -> find(array('state = ' . Cron::STATE_PENDING, 'name = ' . self::FB_TASK_NAME));

		if ($tasks) {
			foreach ($tasks as $task) {
				$args = unserialize($task -> parameters);
				/*if ($task -> delete() === false) {
			        echo "Sorry, I can't delete the task right now: \n";
			    } else {
			        print_r("The task was deleted successfully! \n\r");
			    }
			    foreach ($task -> getMessages() as $message) {
		            echo ($message . "\n");
		        }*/
		        $task -> state = Cron::STATE_HANDLING;
		        $task -> update();
		        
				$this -> console -> handle(['task' => 'harvester', 
											'action' => 'harvest',
											'params' => [$args['user_token'], $args['user_fb_uid'], $args['member_id']]]);
			}
		}
	}

	public function testAction(array $args)
	{
		echo sprintf('Token is %s', $args[0]) . PHP_EOL;
		echo sprintf('AccId is %s', $args[1]) . PHP_EOL;
		echo "\n\n";
	}

}