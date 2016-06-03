<?php

namespace Tasks\Facebook\Venue;

use \Vendor\FacebookGraph\FacebookSession,
	\Vendor\FacebookGraph\FacebookRequest,
	\Vendor\FacebookGraph\FacebookRequestException,
	\Queue\Producer\Producer,
	\Queue\Consumer\Consumer,
	\Models\Cron,
	\Models\Venue,
	\Models\Event;
	
class GrabTask extends \Phalcon\CLI\Task
{
	use \Tasks\Facebook\Grabable;
	
	protected $fbSession;
	protected $fbAppAccessToken;
	protected $queueSource;
	protected $queueData;
	protected $photosLimit	= 3;
	

	public function initVendors()
	{
		$this -> initListener('harvesterVenues', 'queueSource');
		$this -> initQueue('harvesterVenuesData', 'queueData');
		$this -> initGraph();
		$this -> setGraphAuthAccessToken();
	}
	
	
	public function listenQueueAction(array $args = [])
	{
		$this -> initVendors();

		while (true) {
			$venue = $this -> queueSource -> getItem();
		
			if($venue) {
				$this -> queueSource -> ackItem($venue);
				$msg = unserialize($venue -> getBody());
				$item = $msg['item'];
				
				$this -> harvestAction($item, $args);
			} else {
				print_r("\n\rno venues in queue\n\r");
				die();
			}
		}
	}
	
	
	public function listenTablesAction(array $args = [])
	{
		$this -> initVendors();
	
		$venues = Venue::find(['fb_uid is not null limit 500']);
		foreach ($venues as $venueObj) {
			$this -> harvestAction($venueObj -> toArray());	
		}
		
		print_r("\n\rdone\n\r");
		die();
	}
		
	
	public function harvestAction($item, $args = [])
	{
		// get venue info
		//$request = '/' . $item['fb_uid'] . '&access_token=' . $args[0];
		$request = '/' . $item['fb_uid'] . '?access_token=' . $this -> fbAppAccessToken;
print_r("\n\r" . $request . "\n\r");
		try {
			$request = new FacebookRequest($this -> fbSession, 'GET', $request);
			$venue = $request -> execute() -> getGraphObject() -> asArray();
// print_r($venue); die();
			if (!empty($venue)) {
				if ($venue['is_community_page'] != 1) {
					// get uploaded photos
					//$photosRequest = '/' . $item['fb_uid'] . '/photos?type=uploaded&fields=images&limit=' . $this -> photosLimit . '&access_token=' . $args[0];
					$photosRequest = '/' . $item['fb_uid'] . '/photos?type=uploaded&fields=images&limit=' . $this -> photosLimit . '&access_token=' . $this -> fbAppAccessToken;
					$photosRequest = new FacebookRequest($this -> fbSession, 'GET', $photosRequest);
					$photos = $photosRequest -> execute() -> getGraphObject() -> asArray();
					
					if (!empty($photos)) {
						$venue['photos'] = $photos;
					}
					$this -> publishToVenuesBroker($venue, $args);
				} else {
// 					print_r($item['id'] . ": " . $venue['name'] . "\n\r"); 
// 					$object = Venue::findFirst($item['id']);
// 					$object -> delete();
				}
			}
			
		} catch(FacebookRequestException $ex) {
			$error = json_decode($ex -> getRawResponse());
			print_r("\n\r" . $ex -> getMessage() . "\n\r");
// 			die();
		}
	}
	
	
	protected function publishToVenuesBroker($venue, $args = [])
	{
		$data = ['args' => $args,
				 'item' => $venue];
		$this -> queueData -> publish(serialize($data));
	} 
}