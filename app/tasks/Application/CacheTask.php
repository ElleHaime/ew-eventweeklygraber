<?php

namespace Tasks\Application;

use \Models\Cron;

class CacheTask extends \Phalcon\CLI\Task
{
	public function countersAction($args) 
	{
		$t = new \Jobs\Application\Cacher\Counters($this -> getDi());
		$t -> run($args[0]);
	}
	
	public function cacheAction()
	{
		while (true) {
			$t = new \Jobs\Application\Cacher\Totalcache($this -> getDi());
			$t -> run();
			
			sleep(60);
		} 
	}	
}