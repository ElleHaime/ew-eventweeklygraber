<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag;
use Models\Tag;

class Facebook
{
	use \Jobs\Grabber\Parser\Helper;
		
	public $cacheData;
	private $fbUidCachePrefix = 'fbUid';


	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
        $this -> config = $dependencyInjector -> get('config');
        
        if (isset($this -> config -> cache -> prefixes -> fbUid)) {
        	$this -> fbUidCachePrefix = $this -> config -> cache -> prefixes -> fbUid;
        }
	}

	public function run(\AMQPEnvelope $data)
	{
		error_reporting(E_ALL & ~E_NOTICE);
		
		$msg = unserialize($data -> getBody());
		$ev = $msg['item'];
		$needHandle = true;

		if ($msg['type'] == 'user_event' || $msg['type'] == 'user_page_event') {
			$objM = \Models\Member::findFirst($msg['args'][2]);
			if (!$objM) {
				$needHandle = false;
			}
		} 
		
		if ($needHandle) {
			if (!$this -> cacheData -> exists($this -> fbUidCachePrefix . $ev['eid']))
	        {
//print_r("new event \n\r");	        	
	            $result = $eventCategories = $eventTags = [];
	            
	            $result['fb_uid'] = $ev['eid'];
	            $result['fb_creator_uid'] = $ev['creator'];
	            $result['description'] = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $ev['description']);
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
	
	            if ($fbMember = \Models\MemberNetwork::findFirst('account_uid = "' . $ev['creator'] . '"')) {
	            	if ($fbMember -> member_id != $msg['args'][2]) {
	            		$result['member_id'] = $fbMember -> member_id;
	            		
	            		$objC = \Models\EventMemberCounter::findFirst('member_id = ' . $fbMember -> member_id);
	            		$objC -> userEventsCreated =  $objC -> userEventsCreated + 1;
	            		$objC -> update();
	            	}
	            }
	
	            $result['location_id'] = '0';
	            $venueCreated = false;
	
	            if (isset($ev['venue']['id']) && $this -> cacheData -> exists('venue_' . $ev['venue']['id'])) {
	                $venue = $this -> cacheData -> get($this -> fbUidCachePrefix . 'venue_' . $ev['venue']['id']);
	                $result['venue_id'] = $venue['venue_id'];
	                $result['address'] = $venue['address'];
	                $result['latitude'] = $venue['latitude'];
	                $result['longitude'] = $venue['longitude'];
	                $result['location_id'] = $venue['location_id'];
	            } else {
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
	                			$result['latitude'] = ($locExists['latMin'] + $locExists['latMax']) / 2;
	                			$result['longitude'] = ($locExists['lonMin'] + $locExists['lonMax']) / 2;
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
	                        $this -> cacheData -> save($this -> fbUidCachePrefix . 'venue_' . $venueObj -> fb_uid, 
	                                                array('venue_id' => $venueObj -> id,
	                                                      'address' => $venueObj -> address,
	                                                      'location_id' => $venueObj -> location_id,
	                                                      'latitude' => $venueObj->latitude,
	                                                      'longitude' => $venueObj->longitude));
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
	
	            $eventObj = (new \Models\Event())-> setShardByCriteria($result['location_id']);
	            $eventObj -> assign($result);
	
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
	
	                $this -> cacheData -> save($this -> fbUidCachePrefix . $eventObj -> fb_uid, $eventObj -> id);
	                $newEvents[$eventObj -> fb_uid] = $eventObj;
	            } else {
///print_r("ooooooops, not saved\n\r");	            	
	            }
	        } else {
//print_r("exists already\n\r");	        	
	            $newEvents[$ev['eid']] = $eventExists;
	        }
		}

		if (!empty($newEvents)) {
//print_r($msg['type'] . "\n\r");			
        	switch ($msg['type']) {
        		case 'friend_going_event':
        		case 'friend_event':
        				foreach ($newEvents as $ev => $event) {
        					if (!\Models\EventMemberFriend::findFirst('member_id = ' . $msg['args'][2] . ' AND event_id = "' . $event -> id . '"')) {
                            	if ($needHandle) {
	                                $obj = new \Models\EventMemberFriend();
	                                $obj -> assign(['member_id' => $msg['args'][2],
	                                   			 	'event_id' => $event -> id]);
	                                $obj -> save();
                                }
                            } 
                        }
        			break;

        		case 'user_going_event':
        				foreach ($newEvents as $ev => $event) {
        					if (!\Models\EventMember::findFirst('member_id = ' . $msg['args'][2] . ' AND event_id = "' . $event -> id . '" AND member_status = 1')) {
        						if ($needHandle) {
	                                $obj = new \Models\EventMember();
	                                $obj -> assign(['member_id' => $msg['args'][2],
					                                'event_id' => $id,
					                                'member_status' => 1]);
	                                $obj -> save();
                                }
                            }
                        }
        			break;

        		case 'page_event':
        				foreach ($newEvents as $ev => $event) {
        					if (!\Models\EventLike::findFirst('member_id = ' . $msg['args'][2] . ' AND event_id = "' . $event -> id . '" AND status = 1')) {
        						if ($needHandle) {
		                            $obj = new \Models\EventLike();
		                            $obj -> assign(['member_id' => $msg['args'][2],
	                                                'event_id' => $id,
	                                                'status' => 1]);
		                            $obj -> save();
                                }
	                        }
	                    }
        			break;

                case 'user_page_event':
                case 'user_event':
                        foreach ($newEvents as $ev => $event) {
                        	$obj = (new \Models\Event()) -> setShardByCriteria($event -> location_id);
                            if (!$obj::findFirst('member_id = ' . $msg['args'][2] . ' AND id = "' . $event -> id . '"')) {
                            	if ($needHandle) {
	                                $obj -> member_id = $msg['args'][2];
	                                $obj -> update();
                                }  
                            } 
        				}
                    break;
                
                default:
                	print_r("cannot identify event type::" . $event -> id . "\n\r");
        	}
        }
	}
}