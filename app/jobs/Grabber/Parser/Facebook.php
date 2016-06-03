<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag,
	Models\Tag,
	Models\Event,
	Models\Cron,
	Models\Venue,
	Models\Location,
	Queue\Producer\Producer;

class Facebook
{
	use \Jobs\Grabber\Parser\Helper;
	use \Tasks\Facebook\Grabable;
		
	protected $_di;
	protected $queue;


	public function __construct(\Phalcon\DI $dependencyInjector)
	{
        $this -> config = $dependencyInjector -> get('config');
        $this->_di = $dependencyInjector;
	}

	
	public function run(\AMQPEnvelope $data)
	{
		error_reporting(E_ALL & ~E_NOTICE);
		
		$this -> initQueue('harvesterVenues');
		
		$msg = unserialize($data -> getBody());
		$ev = $msg['item'];
		$eventObj = false;
		$newEventCreated = false;

print_r("type: " . $msg['type'] . "\n\r");
//print_r("member: " . $msg['args'][2] . "\n\r");
	
		if (!isset($ev['eid']) && isset($ev['id'])) {
			$ev['eid'] = $ev['id'];
		}	
		$eventObj = (new \Models\Event()) -> existsInShardsBySourceId($ev['eid'], 'fb');
		
		if (!$eventObj) {
            $result = [];
            $eventCategories = [];
            $eventTags = [];
            
            $result['fb_uid'] = $ev['eid'];
            $result['deleted'] = "0";
            $result['fb_creator_uid'] = $ev['fb_creator_uid'];
            $result['description'] = preg_replace('/<a[^>]*>((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.#?=-]*)*\/?)<\/a>/ui', '<a href="$1" target="_blank">$1</a>', $ev['description']);
            $result['name'] = $ev['name'];
            $result['address'] = '';
	            
            if ($result['fb_creator_uid'] == $msg['args'][1]) {
            	$result['member_id'] = $msg['args'][2];
            }
            
            if (!empty($ev['ticket_uri'])) {
                $result['tickets_url'] = $ev['ticket_uri'];
            }

            if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
                $ext = explode('.', $ev['pic_big']);
                if (strpos(end($ext), '?')) {
                    $logo = 'fb_' . $ev['eid'] . '.' . substr(end($ext), 0, strpos(end($ext), '?'));
                } else {
                    $logo = 'fb_' . $ev['eid'] . '.' . end($ext);
                }
                $result['logo'] = $logo;
            }
	
            $result = $this -> processDates($result, $ev);

            $result['location_id'] = '0';
	
            if (isset($ev['venue']['id']) && $venue = Venue::findFirst(['fb_uid = "' . $ev['venue']['id'] . '"'])) {
                $result['venue_id'] = $venue -> id;
                $result['address'] = $venue -> address;
                $result['latitude'] = $venue -> latitude;
                $result['longitude'] = $venue -> longitude;
                $result['location_id'] = $venue -> location_id;
            } else {
            	if (isset($ev['location']) && !isset($ev['venue'])) {
            		$ev['venue'] = $ev['location'];
            	}
            		
            	if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude'])) {
                	$locations = new Location();
                	$locExists = $locations -> createOnChange($ev['venue']);

                	if ($locExists) {
                		$result['location_id'] = $locExists -> id;
                		
                		if (isset($ev['venue']['street'])) {
                			$result['latitude'] = $ev['venue']['latitude'];
                			$result['longitude'] = $ev['venue']['longitude'];
                			if (!empty($ev['venue']['street'])) {
                				$result['address'] = $ev['venue']['street'];
                			} elseif(!empty($ev['location']))  {
                				$result['address'] = $ev['location'];
                			}
                		} else {
                			$result['latitude'] = $locExists -> getCenterLat();
                			$result['longitude'] = $locExists -> getCenterLng();
                		}
                	}
                }
	
                if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude']) && isset($ev['venue']['id'])) {
                    $venueObj = new \Models\Venue();
                    isset($ev['location']) ? $venueName = $ev['location'] : $venueName = '';
                    $venueObj -> assign([
                            'fb_uid' => $ev['venue']['id'],
                            'location_id' => $result['location_id'],
                            'name' => $venueName,
                            'address' => $result['address'],
                            'latitude' => $ev['venue']['latitude'],
                            'longitude' => $ev['venue']['longitude']]);

                    if ($venueObj -> save()) {
	                    $result['venue_id'] = $venueObj -> id;
	                    $result['address'] = $venueObj -> address;
	                    
	                    $this -> queue -> publish(serialize([$venueObj -> id => $venueObj -> fb_uid]));
                    }
                }
            }
	
            if (is_array($result['address'])) {
                $result['address'] = '';
            	$result['location_id'] = 0;
            }
            if (empty($result['location_id']) || is_null($result['location_id'])) {
            }
print_r("location id: " . $result['location_id'] . "\n\r");	
            $eventObj = (new \Models\Event())-> setShardByCriteria($result['location_id']);
            $eventObj -> assign($result);
print_r($eventObj -> name . " | " . $eventObj -> start_date); 
print_r("\n\r");	
            if ($eventObj -> save() != false) {
print_r($eventObj -> id . " saved\n\r");
				$this -> categorize($eventObj);
	            	 
                if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
                    $this -> saveEventImage('fb', $ev['pic_big'], $eventObj);
                }
                if (isset($ev['pic_cover']) && !empty($ev['pic_cover'])) {
                    $this -> saveEventImage('fb', $ev['pic_cover']['source'], $eventObj, 'cover');
                }
                $this -> addToIndex($eventObj);
            } else {
print_r("ooooooops, not saved\n\r");	            	
            }
        } else {
print_r($eventObj -> fb_uid . " exists already with venue " . $eventObj -> venue_id . " and location " . $eventObj -> location_id . "\n\r");

			if ($this -> config -> fixMode -> facebookEventLocation) {			
				// check is event has appropriate venue fb_uid and update if not
				if ($msg['type'] == Cron::FB_CREATOR_VENUE_TASK_TYPE || $msg['type'] == Cron::FB_CREATOR_TASK_TYPE) {
print_r($ev['fb_creator_uid'] . " venue for existenz\n\r");
	
					if (isset($ev['location']) && !isset($ev['venue'])) {
						$ev['venue'] = $ev['location'];
					}
					
					// check and fix location if not exists
					if (isset($ev['venue']['latitude']) && empty($eventObj -> location_id)) {
						$locations = new Location();
						$locExists = $locations -> createOnChange($ev['venue']);
						
print_r("try to create for existenz\n\r");
						if ($locExists) {
print_r("ready, location " . $locExists -> id. " found for existenz\n\r");						
							if ($eventObjNew = (new Event()) -> transferEventBetweenShards($eventObj, $locExists -> id)) {
print_r("existenz old id " . $eventObj -> id . "\n\r");							
								$eventObj = $eventObjNew;
print_r("existenz new id " . $eventObj -> id . "\n\r");
							} 
	                	}
					}
					
					// check and fix venue
					if (isset($ev['venue']['id'])) {
						$venueId = Venue::findFirst(['fb_uid = "' . $ev['venue']['id'] . '"']);
print_r("found venue for existenz with id " . $venueId -> id . "\n\r");					
						if ($venueId) {
							$eventObj -> setShardById($eventObj -> id);
							$eventObj -> venue_id = $venueId -> id;
							if (!$eventObj -> update()) {
								print_r($ev['fb_creator_uid'] . ": ooops, venue not updated\n\r");
							}
						}
					} else {
						print_r($ev['fb_creator_uid'] . ": no venue in existenz\n\r");
					}
				}
				$this -> addToIndex($eventObj);
			}			
        }
        
        $newEventCreated = $eventObj;
		
		if ($newEventCreated) {
			$this -> checkIsMemberOwn($newEventCreated, $msg['args'][1], $msg['args'][2]);
			
        	switch ($msg['type']) {
        		case Cron::FB_USER_FRIEND_GOING_TASK_TYPE:
        		case Cron::FB_USER_FRIEND_TASK_TYPE:
      					if (!\Models\EventMemberFriend::findFirst(['member_id = ' . $msg['args'][2] . ' AND event_id = "' . $newEventCreated -> id . '"'])) {
							$obj = new \Models\EventMemberFriend();
							$obj -> assign(['member_id' => $msg['args'][2],
                                   			'event_id' => $newEventCreated -> id]);
							$obj -> save();
						} 
        			break;

        		case Cron::FB_USER_GOING_TASK_TYPE:
						if (!\Models\EventMember::findFirst(['member_id = ' . $msg['args'][2] . ' AND event_id = "' . $newEventCreated -> id . '" AND member_status = 1'])) {
							$obj = new \Models\EventMember();
							$obj -> assign(['member_id' => $msg['args'][2],
				                            'event_id' => $newEventCreated -> id,
				                            'member_status' => 1]);
							$obj -> save();
						}
        			break;

        		case Cron::FB_USER_LIKE_TASK_TYPE:
						if (!\Models\EventLike::findFirst(['member_id = ' . $msg['args'][2] . ' AND event_id = "' . $newEventCreated -> id . '" AND status = 1'])) {
							$obj = new \Models\EventLike();
							$obj -> assign(['member_id' => $msg['args'][2],
                                            'event_id' => $newEventCreated -> id,
                                            'status' => 1]);
							$obj -> save();
						}
        			break;
        	}
        }
print_r("\n\r\n\r");        
	}
	
	
	public function checkIsMemberOwn($newEventCreated, $memberFbUid, $memberId)
	{
		if ($newEventCreated -> fb_creator_uid == $memberFbUid && $newEventCreated -> member_id != $memberId) {
			if (empty($newEventCreated -> location_id) || is_null($newEventCreated -> location_id)) {
				$newEventCreated -> location_id = 0;
			}
			 
			$obj = (new \Models\Event()) -> setShardByCriteria($newEventCreated -> location_id);
			$e = $obj::findFirst('id = "' . $newEventCreated -> id . '"');
		
			$e -> setShardByCriteria($newEventCreated -> location_id);
			$e -> member_id = $memberId;
			$e -> update();
			 
			$grid = new \Models\Event\Grid\Search\Event(['location' => $e -> location_id], $this -> _di, null, ['adapter' => 'dbMaster']);
			$indexer = new \Models\Event\Search\Indexer($grid);
			$indexer -> setDi($this->_di);
print_r("id to index " . $e -> id . "\n\r");
			if (!$indexer -> updateData($e -> id)) {
				print_r("ooooooops, not updated in index\n\r");
			}
		}
		
		return;
	}
	
	
	public function publishToVenueBroker()
	{
		
	}
}