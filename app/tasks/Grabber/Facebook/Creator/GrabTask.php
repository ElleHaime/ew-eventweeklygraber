<?php

namespace Tasks\Facebook\Creator;

use \Vendor\FacebookGraph\FacebookSession,
	\Vendor\FacebookGraph\FacebookRequest,
	\Vendor\FacebookGraph\FacebookRequestException,
	\Queue\Producer\Producer,
	\Models\Cron,
	\Models\Venue,
	\Models\Event;
	
class GrabTask extends \Phalcon\CLI\Task
{
	use \Tasks\Facebook\Grabable;
	
	protected $fbSession;
	protected $fbAppAccessToken;
	protected $queue;
	protected $queueCreators;
	
	protected $searchDataFields	= 'fields=id,owner,start_time,end_time,name,location,cover,venue,description,ticket_uri';
	protected $resultType 			= Cron::FB_CREATOR_TASK_TYPE;


	public function harvestVenueAction(array $args)
	{
		$this -> initQueue('harvesterJob');
		$this -> initGraph();
		$this -> fbAppAccessToken = $args[0];
		$this -> resultType = Cron::FB_CREATOR_VENUE_TASK_TYPE;

		$creators = (new Venue()) -> getCreators();

		if (!empty($creators)) {
			foreach ($creators as $val) {
				$this -> harvestEventsAction($val -> fb_uid, $args);
			}
		}
		$this -> killTask($args[3]);
		print_r("\n\r" . date('H:i Y-m-d') . " :: harvest all venues done\n\r");
		die();
	}
	
	
	protected function harvestEventsAction($creatorUid, $args)
	{
		$query = '/' . $creatorUid . '/events?' . $this -> searchDataFields . '&access_token=' . $this -> fbAppAccessToken . '&limit=100';
		try {
			$request = new FacebookRequest($this -> fbSession, 'GET', $query);
			$data = $request -> execute() -> getGraphObject() -> asArray();
		
			if (!empty($data['data'])) {
				foreach ($data['data'] as $event) {
					if (!Event::checkExpirationDate($event -> start_time) || $this -> checkInIndex($event -> id)) continue;

// 					$eventObj =  (new \Models\Event()) -> existsInShardsBySourceId($event -> eid, 'fb');
// 					if($eventObj) continue;
					
					$event -> fb_creator_uid = $creatorUid;
					if (isset($event -> cover))  {
						$event -> pic_cover = $event -> cover;
					}
//print_r($event -> name . "\n\r");		
					print_r(".");			
					$this -> publishToBroker($event, $args, $this -> resultType);
				}
			}
			
		} catch (FacebookRequestException $ex) {
			$error = json_decode($ex -> getRawResponse());
			switch($error -> error -> code) {
				case 100:
					//print_r("\n\rProblem for " . $creatorUid. "\n\r");
					// unsupported get request, m.b. user access token required
						$fp = fopen($this -> config -> facebook -> unsupportedSourceFile, 'a');
						fputcsv($fp, [$creatorUid . ';']);
						fclose($fp);
					break;
				default:
						print_r($ex -> getMessage() . "\n\r");
					break;
			}
		}
//print_r("\n\r\n\r");		
		return;
	}

	
	protected function publishToPageBroker($page)
	{
       	$data = ['item' => $page];
        $this -> queueCreators -> publish(serialize($page));
	}
	
}	
