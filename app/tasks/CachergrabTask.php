<?php

namespace Tasks;

use \Models\Cron;

class cachergrabTask extends \Phalcon\CLI\Task
{
	public function countersAction($args) 
	{
		$t = new \Jobs\Grabber\Cacher\Counters($this -> getDi());
		$t -> run($args[0]);
	}
	
	public function cacheAction()
	{
		while (true) {
			$t = new \Jobs\Grabber\Cacher\Totalcache($this -> getDi());
			$t -> run();
			
			sleep(60);
		} 
	}
}