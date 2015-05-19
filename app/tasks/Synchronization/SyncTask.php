<?php

namespace Tasks;

use \Models\Cron;

class syncTask extends \Phalcon\CLI\Task
{
	public function expiredAction() 
	{
		$job = new \Jobs\Grabber\Sync\Expired($this -> getDi());
		$job -> run();
	}
}