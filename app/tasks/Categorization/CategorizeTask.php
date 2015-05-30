<?php

namespace Tasks\Categorization;

use \Models\Cron;

class CategorizeTask extends \Phalcon\CLI\Task
{
	public function recatAction(array $args)
	{
		$job = new \Jobs\Grabber\Categorize\Categorize($this -> getDi());
		$job -> run();
	}
}
