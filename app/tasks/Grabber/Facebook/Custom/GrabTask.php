<?php

namespace Tasks\Facebook\Custom;

use \Vendor\Facebook\Extractor,
	\Queue\Producer\Producer,
	\Models\Cron;


class GrabTask extends \Phalcon\CLI\Task
{
	use \Tasks\Facebook\Grabable;
	
	const IDLE 					= 'idle';
	const RUNNING 				= 'running';
	const READ_SOURCE_FILE		= 1;
	const READ_SOURCE_DATABASE	= 2;
	
	protected $state = self::IDLE;
	protected $fb;
	protected $queue;
	protected $sourceType = 1;
	protected $searchQuery = 'search?type=event&';


	public function harvestAction(array $args)
	{
		$this -> initQueue('harvesterCustom');
		
		if ($this -> sourceType == self::READ_SOURCE_FILE) {
			$queries = $this -> parseQueries();
		}

		if (!empty($queries)) {
			foreach($queries as $query) {
				$request = $this -> searchQuery . $query;
				
				$request = new FacebookRequest($this -> fbSession, 'GET', $request);
				$data = $request -> execute() -> getGraphObject() -> asArray();
print_r(count($data['data']));
print_r("\n\r");
print_r($data);
print_r("\n\r");
die();
			}
		}
	}
	
	
	protected function parseQueries()
	{
		$result = [];
		
		$qSource = file_get_contents($this -> config -> facebook -> querySourceFile);
		if (strlen($qSource) > 0) {
			$queries = explode(';', $qSource);
			foreach ($queries as $q) {
				if (strlen($q) > 0) {
					$result[] = trim($q);
				}
			}
		}
		
		return $result;
	}
	
}