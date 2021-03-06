<?php

namespace Tasks\Synchronization;

use \Models\Cron;

class SyncTask extends \Phalcon\CLI\Task
{
	public function expiredAction() 
	{
		$job = new \Jobs\Grabber\Sync\Expired($this -> getDi());
		$job -> run();
	}
	
	public function moveImagesAction()
	{
		$job = new \Jobs\Grabber\Sync\EventImages($this -> getDi());
		$job -> run();
	}
}