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
	
		$venues = Venue::find(['fb_uid is not null and fb_username is null and location_id = 1 limit 500']);
		foreach ($venues as $venueObj) {
print_r($venueObj -> id . "::" . $venueObj -> fb_uid . "\n\r");			
			$this -> harvestAction($venueObj -> toArray());	
		}
		
		print_r("\n\rdone\n\r");
		die();
	}
		
	
	public function harvestAction($item, $args = [])
	{
		$request = '/' . $item['fb_uid'] . '?access_token=' . $this -> fbAppAccessToken;

		try {
			$request = new FacebookRequest($this -> fbSession, 'GET', $request);
			$venue = $request -> execute() -> getGraphObject() -> asArray();

			if (!empty($venue)) {
				if ($venue['is_community_page'] != 1) {
					// get logo
					$logoRequest = '/' . $item['fb_uid'] . '/photos?type=profile&fields=images&limit=1&access_token=' . $this -> fbAppAccessToken;
					$logoRequest = new FacebookRequest($this -> fbSession, 'GET', $logoRequest);
					$logoImages = $logoRequest -> execute() -> getGraphObject() -> asArray();
					if (!empty($logoImages)) {
						$venue['logo'] = $logoImages['data'][0] -> images[0] -> source; 
					}

					// get uploaded photos
					$photosRequest = '/' . $item['fb_uid'] . '/photos?type=uploaded&fields=images&limit=' . $this -> photosLimit . '&access_token=' . $this -> fbAppAccessToken;
					$photosRequest = new FacebookRequest($this -> fbSession, 'GET', $photosRequest);
					$photosImages = $photosRequest -> execute() -> getGraphObject() -> asArray();
					if (!empty($photosImages)) {
						$venue['photos'] = $photosImages;
					}

					$this -> publishToVenuesBroker($venue, $args);
				} else {
					print_r($item['id'] . " : " . $item['fb_uid'] . " : " . $venue['name'] . "\n\r"); 
					$object = Venue::findFirst($item['id']);
					$object -> delete();
				}
			}
			
		} catch(FacebookRequestException $ex) {
			$error = $ex -> getCode();
			switch ($error) {
				case 100:
					// Object does not exist, cannot be loaded due to missing permissions, or does not support this operation 
						$object = Venue::findFirst($item['id']);
 						$object -> delete();
					break;
					
				case 21:
					// Page was migrated
						$fp = fopen($this -> config -> facebook -> migratedSourceFile, 'a');
						fputcsv($fp, [$item['id']]);
						fclose($fp);
					break;
					
				default:
					print_r("...." . $item['fb_uid'] . " :: \n\r" . $ex -> getMessage() . "\n\r");
			}
		}
	}
	 
	
	protected function publishToVenuesBroker($venue, $args = [])
	{
		$data = ['args' => $args,
				 'item' => $venue];
		$this -> queueData -> publish(serialize($data));
	} 
}