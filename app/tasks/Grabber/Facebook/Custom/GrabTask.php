<?php

namespace Tasks\Facebook\Custom;

use \Vendor\Facebook\Extractor,
	\Queue\Producer\Producer,
	\Models\Cron;


class GrabTask extends \Phalcon\CLI\Task
{
	use Tasks\Facebook\GrabHepler;
	
	const IDLE 					= 'idle';
	const RUNNING 				= 'running';
	const READ_SOURCE_FILE		= 1;
	const READ_SOURCE_DATABASE	= 2;
	
	protected $state = self::IDLE;
	protected $fb;
	protected $queue;
	protected $sourceType = 1;


	public function harvestAction(array $args)
	{
		$this -> initQueue('harversterCustom');
		
		if ($this -> sourceType == self::READ_SOURCE_FILE) {
			$queries = file_get_contents($this -> config -> facebook -> sourceFile);
			
		} 
	}
}