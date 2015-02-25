<?php

namespace Tasks\Cache;

class FbuidsTask extends \Phalcon\CLI\Task
{
	public function cacheAction() 
	{
		$t = new \Jobs\Cache\Fbeventuids($this -> getDi());
		$t -> run();
	}
}