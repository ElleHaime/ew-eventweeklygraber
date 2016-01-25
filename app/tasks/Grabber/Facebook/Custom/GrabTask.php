<?php

namespace Tasks\Facebook\Custom;

use \Vendor\FacebookGraph\FacebookSession,
	\Vendor\FacebookGraph\FacebookRequest,
	\Vendor\FacebookGraph\FacebookRequestException,
	\Queue\Producer\Producer,
	\Models\Cron,
	\Models\Keyword,
	\Models\Tag,
	\Models\Location,
	\Models\Venue,
	\Models\Grabber;


class GrabTask extends \Phalcon\CLI\Task
{
	use \Tasks\Facebook\Grabable;
	
	const IDLE 						= 'idle';
	const RUNNING 					= 'running';
	
	const READ_SOURCE_FILE			= 1;
	const READ_SOURCE_TABLES		= 2;
	const READ_SOURCE_DATABASE		= 3;
	const READ_EXTRACTED_FILE		= 4;
	
	const MAX_FB_QUERIES_ID			= 300;
	const MAX_FB_QUERIES_DATA		= 1000;
	
	
	protected $fbGraphEnabled		= false;
	protected $fbSession;
	protected $fbAppAccessToken 	= false;
	protected $state 				= self::IDLE;
	protected $fb;
	protected $queue;
	protected $sourceType 			= 1;
	
	protected $queries				= [];
	protected $lastSearchIndex		= 0;
	
// 	protected $searchIdTypes 		= ['tag', 'keyword', 'location'];
	protected $searchIdTypes 		= ['tag', 'keyword'];
	protected $searchIdQuery 		= '/search?type=event&fields=id&';
	protected $searchDataQuery 	= '/';
	protected $searchDataFields 	= 'fields=id,owner,start_time,end_time,name,location,cover,venue,description,ticket_uri';
	
	
	public function harvestidAction(array $args)
	{
		if (!$this -> fbGraphEnabled) { 
			$this -> initGraph();
		}
		
		$lastFetch = Grabber::findFirst('grabber = "facebook" AND type = ' . $this -> sourceType);
		if ($this -> sourceType == self::READ_SOURCE_FILE) {
			$this -> queries = $this -> parseQueries($lastFetch);
		} else {
			$this -> composeQueries($lastFetch);
		}
		
		$queriesCounter = 0;
		foreach($this -> queries as $index => $query) {
			$queriesCounter++;
			if ($queriesCounter > self::MAX_FB_QUERIES_ID) {
				$lastFetch = Grabber::findFirst('grabber = "facebook" AND type = ' . $this -> sourceType);
				$lastFetch -> last_id = $index;
				if ($this -> sourceType == self::READ_SOURCE_FILE) {
					$lastFetch -> value = $query;
				}
				$lastFetch -> update();
			
				$this -> updateTask($args[3], Cron::STATE_INTERRUPTED);
print_r("\n\r" . date('H:i Y-m-d') . "\n\rInterrupted by query limit, app session is going on\n\n");
				die();
			}
			
			$since = time();
			$until = strtotime('+6 month');
			
			$request = $this -> searchIdQuery . 'q=' . $query . '&since=' . $since . '&until=' . $until. '&access_token=' . $args[0];
print_r("....." . $query . "\n\r");			
 			try {
 				$request = new FacebookRequest($this -> fbSession, 'GET', $request);
				$data = $request -> execute() -> getGraphObject() -> asArray();

				if (!empty($data['data'])) {
					$dataString = count($data['data']);

					$fp = fopen($this -> config -> facebook -> idSourceFile, 'a');
					foreach ($data['data'] as $event) {
						if ($this -> checkInIndex($event -> id)) continue;
						fputcsv($fp, [$event -> id]);
					}
					fclose($fp);
				}
			} catch (FacebookRequestException $ex) {
				$lastFetch = Grabber::findFirst('grabber = "facebook" AND type = ' . $this -> sourceType);
				$lastFetch -> last_id = $index;
				$lastFetch -> update();
				
				$error = json_decode($ex -> getRawResponse());
				print_r($ex -> getMessage());
				print_r("\n\r");
				switch($error -> error -> code) {
					case 190:
						// reauth, try to find another access token
						$this -> updateTask($args[3], Cron::STATE_PENDING);
						break;
						
					case 368:
						// misusing, wait 30 minutes and try to find another access token
						$this -> updateTask($args[3], Cron::STATE_INTERRUPTED);
						break;
				}
				print_r("\n\r" . date('H:i Y-m-d') . " :: failed.\n\rStatus: " . $ex -> getMessage() . "\n\r");				
				die();
			}
		}

		if ($this -> sourceType == self::READ_SOURCE_TABLES) {
			if ($this -> lastSearchIndex < count($this -> searchIdTypes)-1) {
				$this -> lastSearchIndex++;
				
				$lastFetch = Grabber::findFirst('grabber = "facebook" AND type = ' . $this -> sourceType);
				$lastFetch -> value = $this -> searchIdTypes[$this -> lastSearchIndex];
				$lastFetch -> last_id = 0;					
				$lastFetch -> update();
				
				$this -> composeQueries($lastFetch);
				$this -> harvestidAction($args);
			} else {
				$lastFetch = Grabber::findFirst('grabber = "facebook" AND type = ' . $this -> sourceType);
				$lastFetch -> value = $this -> searchIdTypes[0];
				$lastFetch -> last_id = 0;					
				$lastFetch -> update();
				
				$this -> initDataGrab($args);
			}
		} else {
			$lastFetch -> value = 'Dublin';
			$lastFetch -> last_id = 0;
			$lastFetch -> update();
			
			$this -> initDataGrab($args);			
		}
		
		print_r("\n\r" . date('H:i Y-m-d') . " :: harvest IDs done\n\r");
		die();
	}
	
	
	public function harvestdataAction(array $args)
	{
		$this -> initQueue('harvesterJob');
		$this -> initGraph();
		$this -> sourceType = self::READ_EXTRACTED_FILE;
	
		$lastFetch = Grabber::findFirst('grabber = "facebook" AND type = ' . $this -> sourceType);
		$queriesCounter = 0;
		
		$source = fopen($this -> config -> facebook -> idSourceFile, 'r');
		fseek($source, $lastFetch -> last_id);
		while (!feof($source)) {
			$query = fgets($source);
			$lastFetch -> last_id = ftell($source);
			$queriesCounter++;
			
			if ($queriesCounter > self::MAX_FB_QUERIES_DATA) {
				$lastFetch -> value = $query;
				$lastFetch -> update();
				$this -> updateTask($args[3], Cron::STATE_INTERRUPTED);
				fclose($source);
print_r("\n\r" . date('H:i Y-m-d') . "\n\rInterrupted by query limit, app session is going on\n\n");
				die();
			}
			$request = $this -> searchDataQuery . trim($query) . '?access_token=' . $args[0] . '&' . $this -> searchDataFields;
print_r("....." . $query . "\n\r");
			try {
				$request = new FacebookRequest($this -> fbSession, 'GET', $request);
				$event = $request -> execute() -> getGraphObject() -> asArray();

				if (isset($event['owner'])) {
					$event['creator'] = $event['owner'] -> id;
				}
				if (!empty($event)) {
					if (isset($event['cover'])) {
						$event['pic_cover'] = $event['cover'];
					}
					$ev['eid'] = $event['id'];
					$this -> publishToBroker($event, $args, 'custom');
				}
			} catch (FacebookRequestException $ex) {
				print_r($ex -> getMessage() . "\n\r");
			}
		}
		fclose($source);
		$source = fopen($this -> config -> facebook -> idSourceFile, 'w+');
		fclose($source);
		
		$lastFetch -> value = 'Dublin';
		$lastFetch -> last_id = 0;
		$lastFetch -> update();
		
		$this -> closeTask($args[3]);
	}

	
	protected function composeQueries($lastFetch = false)
	{
		if ($lastFetch) {
			switch ($lastFetch -> value) {
				case $this -> searchIdTypes[0]:
						 $this -> queries = $this -> getTags($lastFetch -> last_id);
						 $this -> lastSearchIndex = 0;
					 break;
				case $this -> searchIdTypes[1]:
						 $this -> queries = $this -> getKeywords($lastFetch -> last_id);
						 $this -> lastSearchIndex = 1;
					 break;
// 				case $this -> searchIdTypes[2]:
// 						 $this -> queries = $this -> getLocations($lastFetch -> last_id);
// 						 $this -> lastSearchIndex = 2;
// 					 break;
				default:
					$this -> queries = $this -> getTags($lastFetch -> last_id);
					$this -> lastSearchIndex = 0;
			}
		} else {
			$lastFetch = new Grabber();
			$lastFetch -> assign([
				'grabber' => 'facebook',
				'param' => 'id',
				'value' => 'tag',
				'last_id' => 0					
			]);
			$lastFetch -> save();
			
			$this -> queries = $this -> getTags();
			$this -> lastSearchIndex = 0; 
		}
		
		return;
	}
	
	
	protected function parseQueries($lastFetch = false)
	{
		$result = [];
		$offsetFetch = 0;
		
		$qSource = file_get_contents($this -> config -> facebook -> querySourceFile);
		
		if (strlen($qSource) > 0) {
			$source = explode(PHP_EOL, trim($qSource));

			if ($lastFetch) $offsetFetch = $lastFetch -> last_id;
			
			$queries = array_slice($source, $offsetFetch, count($source), true);

			foreach ($queries as $key => $q) {
				if (strlen($q) > 0) {
					$result[$key] = trim($q);
				}
			}
		}
		
		return $result;
	}
	
	
	protected function getKeywords($offset = 0)
	{
		$result = [];
		
		$keywords = Keyword::find(["id >= " . (int)$offset]);
		if ($keywords) {
			foreach ($keywords as $key) {
				$result[$key -> id] = $key -> key; 
			}
		}

		return $result;
	}
	
	
	protected function getTags($offset = 0)
	{
		$result = [];
		
		$tags = Tag::find(["id >= " . (int)$offset]);
		if ($tags) {
			foreach ($tags as $key) {
				$result[$key -> id] = $key -> name; 
			}
		}
		
		return $result;
	}
	
	
	
	protected function getLocations($offset = 0)
	{
		$result = [];
		
		$locations = Location::find(["id >= " . (int)$offset]);
		if ($locations) {
			foreach ($locations as $key) {
				$result[$key -> id] = $key -> city; 
			}
		}
		
		return $result;
	}
	
	
	protected function getVenues($offset = 0)
	{
		$result = [];
		
		$venues = Venue::find(["id >= " . (int)$offset]);
		if ($venues) {
			foreach ($venues as $key) {
				$result[$key -> id] = $key -> name; 
			}
		}
		
		return $result;
	}
	
	protected function initDataGrab($args)
	{
		$taskData = Cron::findFirst($args[3]);
		
		// create task to process ids
		$taskData -> name = Cron::FB_BY_ID_TASK_NAME;
		$taskData -> state = Cron::STATE_PENDING;
		$taskData -> hash = time();
		$taskData -> update();
	}
	
}