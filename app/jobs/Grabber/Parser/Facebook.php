<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag;
use Models\Tag;

class Facebook
{
	use \Jobs\Grabber\Parser\Helper;
		
	private $_di;


	public function __construct(\Phalcon\DI $dependencyInjector)
	{
        $this -> config = $dependencyInjector -> get('config');
        $this->_di = $dependencyInjector;
	}

	public function run(\AMQPEnvelope $data)
	{
		error_reporting(E_ALL & ~E_NOTICE);
		
		$msg = unserialize($data -> getBody());
		$ev = $msg['item'];

print_r("type: " . $msg['type'] . "\n\r");
print_r("member: " . $msg['args'][2] . "\n\r");
		
			if (!isset($ev['eid']) && isset($ev['id'])) {
				$ev['eid'] = $ev['id'];
			}	
		
			$eventObj = (new \Models\Event()) -> existsInShardsBySourceId($ev['eid'], 'fb');

			if (!$eventObj) {
	            $result = $eventCategories = $eventTags = [];
	            $result['fb_uid'] = $ev['eid'];
	            $result['deleted'] = "0";
	            $result['fb_creator_uid'] = $ev['creator'];
	            $result['description'] = preg_replace('/<a[^>]*>((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.#?=-]*)*\/?)<\/a>/ui', '<a href="$1" target="_blank">$1</a>', $ev['description']);
	            $result['name'] = $ev['name'];
	            $result['address'] = '';
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
	
	            if(!empty($ev['start_time'])) {
	                $start = explode('T', $ev['start_time']);
	                $result['start_date'] = $start[0];
	                if (isset($start[1])) {
	                    $time = explode('+', $start[1]); 
	                    $result['start_time'] = $time[0];       
	                }
	            }
	
	            if(!empty($ev['end_time'])) {
	                $end = explode('T', $ev['end_time']);
	                $result['end_date'] = $end[0];
	                if (isset($end[1])) {
	                    $time = explode('+', $end[1]);
	                    $result['end_time'] = $time[0];
	                }
	            }
	
	            $result = $this -> processDates($result);
	
	            $result['location_id'] = '0';
	            $venueCreated = false;
	
	            if (isset($ev['venue']['id']) && $venue = \Models\Venue::findFirst('fb_uid = "' . $ev['venue']['id'] . '"')) {
	            	$venue = $venue -> toArray();
	            	
	                $result['venue_id'] = $venue['venue_id'];
	                $result['address'] = $venue['address'];
	                $result['latitude'] = $venue['latitude'];
	                $result['longitude'] = $venue['longitude'];
	                $result['location_id'] = $venue['location_id'];
	            } else {
	            	if (isset($ev['location']) && !isset($ev['venue'])) {
	            		$ev['venue'] = $ev['location'];
	            	}
	            		
	            	if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude'])) {
	                	$locations = new \Models\Location();
	                	$locExists = $locations -> createOnChange(['latitude' => $ev['venue']['latitude'], 'longitude' => $ev['venue']['longitude']]);

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
	                			//$result['latitude'] = ($locExists['latMin'] + $locExists['latMax']) / 2;
	                			//$result['longitude'] = ($locExists['lonMin'] + $locExists['lonMax']) / 2;
	                			if (isset($locExists -> latitudeMin) && isset($locExists -> latitudeMax) &&
	                					isset($locExists -> longitudeMin) && isset($locExists -> longitudeMax)) {
	                				$result['latitude'] = ($locExists -> latitudeMin + $locExists -> latitudeMax) / 2;
	                				$result['longitude'] = ($locExists -> longitudeMin + $locExists -> longitudeMax) / 2;
	                			}
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
	
	                    if ($venueObj -> save() != false) {
	                        $venueCreated = $venueObj;
	                    }
	                }
	
	                if ($venueCreated !== false) {
	                    $result['venue_id'] = $venueObj -> id;
	                    $result['address'] = $venueObj -> address;
	                } else {
	                    $result['venue_id'] = null;
	                    if (isset($ev['location'])) {
	                        $result['address'] = $ev['location'];
	                    }
	                }
	            }
	
	            if (is_array($result['address'])) {
	                $result['address'] = '';
	            }
	            if (empty($result['location_id']) || is_null($result['location_id'])) {
	            	$result['location_id'] = 0;
	            }
print_r("location id: " . $result['location_id'] . "\n\r");	
	            $eventObj = (new \Models\Event())-> setShardByCriteria($result['location_id']);
	            $eventObj -> assign($result);
//print_r($result);
//print_r("\n\r");	
	            if ($eventObj -> save() != false) {
print_r($eventObj -> id . "saved\n\r");
					$this -> categorize($eventObj);

	            	$total = \Models\Total::findFirst('entity = "event"');
	            	$total -> total = $total -> total + 1;
	            	$total -> update();
	            	 
	                if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
	                    $this -> saveEventImage('fb', $ev['pic_big'], $eventObj);
	                }
	                if (isset($ev['pic_cover']) && !empty($ev['pic_cover'])) {
	                    $this -> saveEventImage('fb', $ev['pic_cover']['source'], $eventObj, 'cover');
	                }
	
	                $newEvents[$eventObj -> fb_uid] = $eventObj;
	                
	                $grid = new \Models\Event\Grid\Search\Event(['location' => $result['location_id']], $this -> _di, null, ['adapter' => 'dbMaster']);
	                $indexer = new \Models\Event\Search\Indexer($grid);
	                $indexer -> setDi($this->_di);
	                if (!$indexer -> addData($eventObj -> id)) {
	                	print_r("ooooooops, not saved to index\n\r");
	                }
	                
	            } else {
print_r("ooooooops, not saved\n\r");	            	
	            }
	        } else {
print_r($eventObj -> fb_uid . " exists already\n\r");	        	
	            $newEvents[$ev['eid']] = $eventObj;
	        }
		
		if (!empty($newEvents)) {
        	switch ($msg['type']) {
        		case 'friend_going_event':
        		case 'friend_event':
        				foreach ($newEvents as $ev => $event) {
        					if (!\Models\EventMemberFriend::findFirst(['member_id = ' . $msg['args'][2] . ' AND event_id = "' . $event -> id . '"'])) {
                                $obj = new \Models\EventMemberFriend();
                                $obj -> assign(['member_id' => $msg['args'][2],
                                   			 	'event_id' => $event -> id]);
                                $obj -> save();
                            } 
                        }
        			break;

        		case 'user_going_event':
        				foreach ($newEvents as $ev => $event) {
        					if (!\Models\EventMember::findFirst(['member_id = ' . $msg['args'][2] . ' AND event_id = "' . $event -> id . '" AND member_status = 1'])) {
                                $obj = new \Models\EventMember();
                                $obj -> assign(['member_id' => $msg['args'][2],
				                                'event_id' => $event -> id,
				                                'member_status' => 1]);
                                $obj -> save();
                            }
                        }
        			break;

        		case 'page_event':
        				foreach ($newEvents as $ev => $event) {
        					if (!\Models\EventLike::findFirst(['member_id = ' . $msg['args'][2] . ' AND event_id = "' . $event -> id . '" AND status = 1'])) {
	                            $obj = new \Models\EventLike();
	                            $obj -> assign(['member_id' => $msg['args'][2],
                                                'event_id' => $event -> id,
                                                'status' => 1]);
	                            $obj -> save();
	                        }
	                    }
        			break;

                case 'user_page_event':
                case 'user_event':
                        foreach ($newEvents as $ev => $event) {
                        	if (empty($event -> location_id) || is_null($event -> location_id)) {
                        		$event -> location_id = 0;
                        	}
print_r("member event: location " . $event -> location_id . "\n\r");                        	
                        	$obj = (new \Models\Event()) -> setShardByCriteria($event -> location_id);
                            $e = $obj::findFirst('id = "' . $event -> id . '"');
                            if ($e) {
                           		$e -> setShardByCriteria($event -> location_id);
                                $e -> member_id = $msg['args'][2];
                                $e -> update();
                                
                                $grid = new \Models\Event\Grid\Search\Event(['location' => $e -> location_id], $this -> _di, null, ['adapter' => 'dbMaster']);
                                $indexer = new \Models\Event\Search\Indexer($grid);
                                $indexer -> setDi($this->_di);
print_r("id to index " . $e -> id . "\n\r");                               
                                if (!$indexer -> updateData($e -> id)) {
                                	print_r("ooooooops, not updated in index\n\r");
                                }
                            } 
        				}
                    break;
        	}
        }
	}
}








