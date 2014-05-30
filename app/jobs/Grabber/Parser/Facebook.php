<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag;
use Models\Tag;

class Facebook
{
    public $cacheData;


	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
        $this -> config = $dependencyInjector -> get('config');
	}

	public function run(\AMQPEnvelope $data)
	{
		error_reporting(E_ALL & ~E_NOTICE);
		
		$msg = unserialize($data -> getBody());
		$ev = $msg['item'];

		if (!$eventExists = \Models\Event::findFirst('fb_uid = "' . $ev['eid'] . '"'))
        {
            $result = array();
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

            if (isset($result['start_date']) && isset($result['end_date'])) {
                if (isset($result['start_time']) && isset($result['end_time'])) {

                    $result['start_date'] = $result['start_date'] . ' ' . $result['start_time'];

                    if(strtotime($result['start_date'] . ' ' . $result['start_time']) >= strtotime($result['end_date'] . ' ' . $result['end_time'])) {
                        $result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));
                    } else {
                        $result['end_date'] = $result['end_date'] . ' ' . $result['end_time'];
                    }
                    unset($result['start_time']);
                    unset($result['end_time']);

                } elseif(isset($result['start_time']) && !isset($result['end_time'])) {
                    $result['start_date'] = $result['start_date'] . ' ' . $result['start_time'];
                    $result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));

                    unset($result['start_time']);

                } elseif(!isset($result['start_time']) && isset($result['end_time'])) {
                    $result['end_date'] = $result['end_date'] . ' ' . $result['end_time'];
                    unset($result['end_time']);
                }
            } elseif (isset($result['start_date']) && !isset($result['end_date'])) {
                $result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));
                if (isset($result['start_time'])) {
                    $result['start_date'] = $result['start_date'] . ' ' . $result['start_time'];    
                    unset($result['start_time']);
                } 
                unset($result['start_time']);
            }


            if ($fbMember = \Models\MemberNetwork::findFirst('account_uid = "' . $ev['creator'] . '"')) {
            	if ($fbMember -> member_id != $msg['args'][2]) {
            		$result['member_id'] = $fbMember -> member_id;
            		
            		$objC = \Models\EventMemberCounter::findFirst('member_id = ' . $fbMember -> member_id);
            		$objC -> userEventsCreated =  $objC -> userEventsCreated + 1;
            		$objC -> update();
            	}
            }

            $result['location_id'] = '';
            $venueCreated = false;

            if (isset($ev['venue']['id']) && $this -> cacheData -> exists('venue_' . $ev['venue']['id'])) {
                $venue = $this -> cacheData -> get('venue_' . $ev['venue']['id']);
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
                        $this -> cacheData -> save('venue_' . $venueObj -> fb_uid, 
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

            $Text = new \Categoryzator\Core\Text();
            if (!empty($result['name'])) {
                $Text -> addContent($result['name']);
            } else {
                $result['name'] = '';
            }
            if (!empty($result['description'])) {
                $Text -> addContent($result['description']);
            } else {
                $result['description'] = '';
            }
            $Text -> returnTag(true);

            $categoryzator = new \Categoryzator\Categoryzator($Text);
            $newText = $categoryzator->analiz(\Categoryzator\Categoryzator::MULTI_CATEGORY);
            $cats = array();
            $tags = array();

            foreach ($newText->category as $key => $c) {
                $Cat = \Models\Category::findFirst('key = \''.$c.'\'');
                if ($Cat) {
                    $cats[$key] = new \Models\EventCategory();
                    $cats[$key]->category_id = $Cat->id;
                }
            }

            foreach ($newText->tag as $c) {
                foreach ($c as $key => $tag) {
                    $Tag = Tag::findFirst('name = \''.$tag.'\'');
                    if ($Tag) {
                        $tags[$key] = new EventTag();
                        $tags[$key]->tag_id = $Tag->id;
                    }
                }
            }

            if (!empty($cats)) {
                $result['event_category'] = $cats;
                $result['event_tag'] = $tags;
            }

            $eventObj = new \Models\Event();
            $eventObj -> assign($result);

            if ($eventObj -> save() != false) {
            	$total = \Models\Total::findFirst('entity = "event"');
            	$total -> total = $total -> total + 1;
            	$total -> update();
            	 
                if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
                    $this -> saveEventImage($ev['pic_big'], $eventObj);
                }
                if (isset($ev['pic_cover']) && !empty($ev['pic_cover'])) {
                    $this -> saveEventImage($ev['pic_cover']['source'], $eventObj, 'cover');
                }

                $this -> cacheData -> save('fbe_' . $eventObj -> fb_uid, $eventObj -> id);
                $newEvents[$eventObj -> fb_uid] = $eventObj -> id;
            }
        } else {
            $newEvents[$ev['eid']] = $eventExists -> id;
        }


		if (!empty($newEvents)) {
        	switch ($msg['type']) {
        		case 'friend_going_event':
        				foreach ($newEvents as $ev => $id) {
        					if (!\Models\EventMemberFriend::findFirst('member_id = ' . $msg['args'][2] . ' AND event_id = ' . $id)) {
                                $obj = new \Models\EventMemberFriend();
                                $obj -> assign(['member_id' => $msg['args'][2],
                                   			 	'event_id' => $id]);
                                $obj -> save();
                                
                                $objC = \Models\EventMemberCounter::findFirst('member_id = ' . $msg['args'][2]);
                                if ($objC) {
	                                $objC -> userFriendsGoing =  $objC -> userFriendsGoing + 1;
	                                $objC -> update();
                                }
                            }
                        }
        			break;

        		case 'user_going_event':
        				foreach ($newEvents as $ev => $id) {
        					if (!\Models\EventMember::findFirst('member_id = ' . $msg['args'][2] . ' AND event_id = ' . $id . ' AND member_status = 1')) {
                                $obj = new \Models\EventMember();
                                $obj -> assign(['member_id' => $msg['args'][2],
				                                'event_id' => $id,
				                                'member_status' => 1]);
                                $obj -> save();
                                
                                $objC = \Models\EventMemberCounter::findFirst('member_id = ' . $msg['args'][2]);
                                if ($objC) {
	                                $objC -> userEventsGoing =  $objC -> userEventsGoing + 1;
	                                $objC -> update();
                                }
                            }
                        }
        			break;

        		case 'page_event':
        				foreach ($newEvents as $ev => $id) {
        					if (!\Models\EventLike::findFirst('member_id = ' . $msg['args'][2] . ' AND event_id = ' . $id . ' AND status = 1')) {
	                            $obj = new \Models\EventLike();
	                            $obj -> assign(['member_id' => $msg['args'][2],
                                                'event_id' => $id,
                                                'status' => 1]);
	                            $obj -> save();
                                
                                $objC = \Models\EventMemberCounter::findFirst('member_id = ' . $msg['args'][2]);
                                if ($objC) {
	                                $objC -> userEventsLiked =  $objC -> userEventsLiked + 1;
	                                $objC -> update();
                                }
	                        }
	                    }
        			break;

                case 'user_page_event':
                case 'user_event':
print_r("user #" . $msg['args'][2] . " event with ");                	
                        foreach ($newEvents as $ev => $id) {
print_r("fb_uid = " . $ev . " and id = " . $id);
                            if (!\Models\Event::findFirst('member_id = ' . $msg['args'][2] . ' AND id = ' . $id)) {
                                $obj = \Models\Event::findFirst($id);
                                $obj -> member_id = $msg['args'][2];
                                $obj -> update();
print_r(" saved to event\n\r");
                                $objC = \Models\EventMemberCounter::findFirst('member_id = ' . $msg['args'][2]);
                                if ($objC) {
print_r("old counter: " . $objC -> userEventsCreated . "\n\r");
                                	$objC -> userEventsCreated =  $objC -> userEventsCreated + 1;
print_r("new counter: " . $objC -> userEventsCreated . "\n\r");
                                	$objC -> update();
                                } 
                            } else { 
print_r(" already exists\n\r");
                            }
                        }
                    break;
        	}
        }        			
	}


    public function saveEventImage($source, \Models\Event $event, $imgType = null, $width = false, $height = false)
    {
        $ext = explode('.', $source);
        if (strpos(end($ext), '?')) {
            $img = 'fb_' . $event -> fb_uid . '.' . substr(end($ext), 0, strpos(end($ext), '?'));
        } else {
            $img = 'fb_' . $event -> fb_uid . '.' . end($ext);
        }

        $ch = curl_init($source);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);

        if (is_null($imgType)) {
            $fDir = $this -> config -> application -> uploadDir . $event -> id;
            $fPath = $this -> config -> application -> uploadDir . $event -> id . '/' . $img;
        } else {
            $fDir = $this -> config -> application -> uploadDir . $event -> id . '/' . $imgType;
            $fPath = $this -> config -> application -> uploadDir . $event -> id . '/' . $imgType . '/' . $img;            
        }

        if ($content) {
            if (!is_dir($fDir)) {
                mkdir($fDir, 0777, true);
            }
            $f = fopen($fPath, 'wb');
            fwrite($f, $content);
            fclose($f);
            chmod($fPath, 0777);
        }

        $images = new \Models\EventImage();
        $images -> assign(array(
                'event_id' => $event -> id,
                'image' => $img,
                'type' => $imgType));
        $images -> save();
    }
}