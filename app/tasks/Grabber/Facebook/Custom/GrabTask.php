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
	\Models\Venue;


class GrabTask extends \Phalcon\CLI\Task
{
	use \Tasks\Facebook\Grabable;
	
	const IDLE 					= 'idle';
	const RUNNING 				= 'running';
	const READ_SOURCE_FILE		= 1;
	const READ_SOURCE_DATABASE	= 2;
	const READ_SOURCE_KEYWORDS	= 3;
	const READ_SOURCE_LOCATION	= 4;
	
	protected $fbSession;
	protected $state = self::IDLE;
	protected $fb;
	protected $queue;
	protected $sourceType = 4;
	protected $searchIdQuery = '/search?type=event&fields=id&';
	protected $searchDataQuery = '/';
	protected $searchDataFields = 'fields=id,owner,start_time,end_time,name,location,cover,venue,description,ticket_uri';
	

	public function harvestidAction(array $args)
	{
		$this -> initGraph();
		
		if ($this -> sourceType == self::READ_SOURCE_FILE) {
			$queries = $this -> parseQueries();
		} elseif($this -> sourceType == self::READ_SOURCE_KEYWORDS) {
			$queries = $this -> getKeywords();
		} elseif($this -> sourceType == self::READ_SOURCE_LOCATION) {
			$queries = $this -> getLocations();
		}

		if (!empty($queries)) {
			$queriesChunked = array_chunk($queries, 200);
			
			foreach ($queriesChunked as $queries) {
				$fq = fopen($this -> config -> facebook -> querySourceFile, 'a');
							
				foreach($queries as $query) {
					$request = $this -> searchIdQuery . $query . '&access_token=' . $args[0];
					$request = new FacebookRequest($this -> fbSession, 'GET', $request);
	
					$data = $request -> execute() -> getGraphObject() -> asArray();
	
					if (!empty($data['data'])) {
						$dataString = count($data['data']);
						$queryString =  $query . ": " . $dataString;				
						fputcsv($fq, [$queryString]);
print_r($queryString);
print_r("\n\r");										
						$fp = fopen($this -> config -> facebook -> idSourceFile, 'a');
						foreach ($data['data'] as $event) {
							fputcsv($fp, [$event -> id]);
						}
						fclose($fp);
					}
				}
				fclose($fq);

print_r("sleeeping....\n\r");
				
				sleep(1800);
			}
		}

print_r("done\n\r");
		$this -> closeTask($args[3]);
	}
	
	
	public function harvestdataAction(array $args)
	{
		$this -> initQueue('harvesterCustom');
		$this -> initGraph();
		
		$queries = $this -> parseIds();
		if (!empty($queries)) {
			foreach($queries as $query) {
				$request = $this -> searchDataQuery . $query . '?access_token=' . $args[0] . '&' . $this -> searchDataFields;
			
				try {
					$request = new FacebookRequest($this -> fbSession, 'GET', $request);
					$event = $request -> execute() -> getGraphObject() -> asArray();

					if (!empty($event)) {
						$event['creator'] = $event['owner'] -> id;
						$event['pic_cover'] = $event['cover'];
				
						$this -> publishToBroker($event, $args, 'custom');
					}
				} catch (FacebookRequestException $ex) {
					print_r($ex -> getMessage() . "\n\r");	
				}
	
			}
		}
		
		$this -> closeTask($args[3]);
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
	
	
	protected function parseIds()
	{
		$result = [];
		
		$fp = fopen($this -> config -> facebook -> idSourceFile, 'r');
		while (($data = fgetcsv($fp)) !== false) {
			$result[] = $data[0];
		}
		fclose($fp);
		
		return $result;
	}
	
	
	protected function getKeywords()
	{
		$result = [];
		
		$keywords = Keyword::find();
		if ($keywords) {
			foreach ($keywords as $key) {
				$result[] = 'q=' . $key -> key; 
			}
		}
		
		$tags = Tag::find();
		if ($tags) {
			foreach ($tags as $key) {
				$result[] = 'q=' . $key -> name; 
			}
		}
		
		return $result;
	}
	
	
	protected function getLocations()
	{
		$result = [];
		
		$keywords = Venue::find();
		if ($keywords) {
			foreach ($keywords as $key) {
				$result[] = 'q=' . $key -> name; 
			}
		}
		
		$tags = Location::find();
		if ($tags) {
			foreach ($tags as $key) {
				$result[] = 'q=' . $key -> city; 
			}
		}
		
		return $result;
	}
	
}