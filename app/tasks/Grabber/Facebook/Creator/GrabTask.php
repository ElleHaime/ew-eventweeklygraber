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
	protected $searchQueryLimit	= '100';
	protected $resultType 			= Cron::FB_CREATOR_TASK_TYPE;
	
	
	public function harvestAction(array $args)
	{
		$this -> initQueue('harvester');
		$this -> initQueue('harvesterCreators', 'queueCreators');
		$this -> initGraph();
		$this -> setGraphSimpleAccessToken();
		
		$creators = (new Event()) -> getCreators();

		if (!empty($creators)) {
			foreach ($creators as $val) {
				// get page info
				$query = '/' . $val;
//print_r($query . "\n\r");				
				try {
					$request = new FacebookRequest($this -> fbSession, 'GET', $query);
					$data = $request -> execute() -> getGraphObject() -> asArray();
//print_r($data);
//print_r("\n\r");
					if (!empty($data)) {
						$this -> publishToPageBroker($data);
					} 
				} catch (FacebookRequestException $ex) {
					print_r($ex -> getMessage() . "\n\r");
				}	
				
				$this -> harvestEventsAction($val);
			} 
		}
		
		$this -> closeTask($args[3]);
print_r("done\n\r");
	}

	
	public function harvestVenueAction($args = false)
	{
		$this -> initQueue('harvester');
		$this -> initGraph();
		$this -> setGraphSimpleAccessToken();
		$this -> resultType = Cron::FB_CREATOR_VENUE_TASK_TYPE;
		
		$creators = (new Venue()) -> getCreators();
		
		if (!empty($creators)) {
			foreach ($creators as $val) {
				$this -> harvestEventsAction($val -> fb_uid, $args);
			}
		}
		//$this -> closeTask($args[0]);
print_r("done\n\r");
	}
	
	
	protected function harvestEventsAction($creatorUid, $args)
	{
		$query = '/' . $creatorUid . '/events?' . $this -> searchDataFields . '&access_token=' . $this -> fbAppAccessToken . '&limit=100';
print_r($query . "\n\r");
		try {
			$request = new FacebookRequest($this -> fbSession, 'GET', $query);
			$data = $request -> execute() -> getGraphObject() -> asArray();
		
			if (!empty($data['data'])) {
				foreach ($data['data'] as $event) {
					if (!Event::checkExpirationDate($event -> start_time)) {
						return;
					}				
					$event -> creator = $creatorUid;
					if (isset($event -> cover))  {
						$event -> pic_cover = $event -> cover;
					}
print_r($event -> name . "\n\r");					
					$this -> publishToBroker($event, $args, $this -> resultType);
				}
			}
			
		} catch (FacebookRequestException $ex) {
			print_r($ex -> getMessage() . "\n\r");
		}
print_r("\n\r\n\r");		
		return;
	}

	
	protected function publishToPageBroker($page)
	{
       	$data = ['item' => $page];
        $this -> queueCreators -> publish(serialize($page));
	}
	
}	
