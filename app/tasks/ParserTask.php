<?php

namespace Tasks;

use \Vendor\Facebook\Extractor,
	\Queue\Consumer\Consumer;


class parserTask extends \Phalcon\CLI\Task
{
	protected $queue;

	public function listenAction()
	{	
		$this -> queue = new Consumer();
		$this -> queue -> connect(['host' => $this -> config -> queue -> host,
								   'port' => $this -> config -> queue -> port,
								   'login' => $this -> config -> queue -> login,
								   'password' => $this -> config -> queue -> password,
								   'exchange' => $this -> config -> queue -> harvester -> exchange,
								   'routing_key' => $this -> config -> queue -> harvester -> routing_key
								  ]);
		$this -> queue -> getQueue();

		while ($envelope = $this -> queue -> getItem()) {
			if($envelope) {
				$this -> queue -> ackItem($envelope);
				$this -> parse($envelope);
			}
		}
	}

	protected function parse($message)
	{
		$msg = unserialize($message -> getBody());
		$ev = $msg['item'];
		$locationsScope = $this -> cacheData -> get('locations');

		if (!$this -> cacheData -> exists('fbe_' . $ev['eid']) && (isset($ev['venue']) && !empty($ev['venue']) || $ev['type'] == 'user_event')) 
        {
            $result = array();
            $result['fb_uid'] = $ev['eid'];
            $result['fb_creator_uid'] = $ev['creator'];
            $result['description'] = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $ev['description']);
            $result['name'] = $ev['name'];

            if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
                $ext = explode('.', $ev['pic_big']);
                $logo = 'fb_' . $ev['eid'] . '.' . end($ext);
                $result['logo'] = $logo;
            }

            if (!empty($ev['start_time'])) {
                $result['start_date'] = date('Y-m-d', strtotime($ev['start_time']));
                $result['start_time'] = date('H:i', strtotime($ev['start_time']));
            }
            if (!empty($ev['end_time'])) {
                $result['end_date'] = date('Y-m-d', strtotime($ev['end_time']));
                $result['end_time'] = date('H:i', strtotime($ev['end_time']));
            }

            if (empty($result['end_date']) && !empty($result['start_date'])) {
                $result['end_date'] = date('Y-m-d H:m:i', strtotime($result['start_date'].' + 1 week'));
            }

            if ($this -> cacheData -> exists('member_' . $ev['creator'])) {
                $result['member_id'] = $this -> cacheData -> get('member_' . $ev['creator']);
            }

            $result['location_id'] = '';
            if (isset($ev['venue']['id']) && !($this -> cacheData -> exists('venue_' . $ev['venue']['id']))) {

                if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude']) && 
                    $ev['venue']['latitude'] != '' && $ev['venue']['longitude'] != '') 
                {

                    if (!empty($locationsScope)) {
                        foreach ($locationsScope as $loc_id => $coords) {
                            if ($ev['venue']['latitude'] >= $coords['latMin'] && $coords['latMax'] >= $ev['venue']['latitude'] &&
                                $ev['venue']['longitude'] <= $coords['lonMax'] && $coords['lonMin'] <= $ev['venue']['longitude'])
                            {
                                $result['location_id'] = $loc_id;

                                if ($ev['venue']['street'] != '') {
                                    $result['latitude'] = $ev['venue']['latitude'];
                                    $result['longitude'] = $ev['venue']['longitude'];
                                } else {
                                    $result['latitude'] = ($coords['latMin'] + $coords['latMax']) / 2;
                                    $result['longitude'] = ($coords['lonMin'] + $coords['lonMax']) / 2;
                                }

                                break;
                            }
                        }
                    }

                    if ($result['location_id'] == '') {
                        $locator = new \Models\Location();
                        $loc = $locator -> createOnChange(array('latitude' => $ev['venue']['latitude'],
                                                                'longitude' => $ev['venue']['longitude']));
                        $locationsScope[$loc -> id] = array('latMin' => $loc -> latitudeMin,
                                                            'lonMin' => $loc -> longitudeMin,
                                                            'latMax' => $loc -> latitudeMax,
                                                            'lonMax' => $loc -> longitudeMax,
                                                            'city' => $loc -> city,
                                                            'country' => $loc -> country);
                         
                        $this -> cacheData -> delete('locations');
                        $this -> cacheData -> save('locations', $locationsScope);  

                        $result['location_id'] = $loc -> id;
                        $result['latitude'] = ($loc -> latitudeMin + $loc -> latitudeMax) / 2;
                        $result['longitude'] = ($loc -> longitudeMin + $loc -> longitudeMax) / 2;
                    }                                
                } 

                if ($ev['venue']['street'] != '') {
                    $venueObj = new \Models\Venue();
                    $venueObj -> assign(array(
                            'fb_uid' => $ev['venue']['id'],
                            'location_id' => $result['location_id'],
                            'name' => $ev['location'],
                            'address' => $ev['venue']['street'],
                            'latitude' => $ev['venue']['latitude'],
                            'longitude' => $ev['venue']['longitude']
                    ));
                    if ($venueObj -> save()) {
                        $result['venue_id'] = $venueObj -> id;
                        $result['address'] = $venueObj -> address;

                        $this -> cacheData -> save('venue_' . $venueObj -> fb_uid, 
                                                array('venue_id' => $venueObj -> id,
                                                      'address' => $venueObj -> address,
                                                      'location_id' => $venueObj -> location_id,
                                                      'latitude' => $venueObj->latitude,
                                                      'longitude' => $venueObj->longitude));
                    }
                }
            } elseif (isset($ev['venue']['id']) && $this -> cacheData -> exists('venue_' . $ev['venue']['id'])) {
                $venue = $this -> cacheData -> get('venue_' . $ev['venue']['id']);
                $result['venue_id'] = $venue['venue_id'];
                $result['address'] = $venue['address'];
                $result['latitude'] = $venue['latitude'];
                $result['longitude'] = $venue['longitude'];
                $result['location_id'] = $venue['location_id'];
            } else {
                if (isset($ev['location']) && $ev['location'] != '' && !empty($locationScope)) 
                {
                    foreach ($locationsScope as $loc_id => $coords) {
                        if (strpos($ev['location'], $coords['city']))
                        {
                            $result['location_id'] = $loc_id;
                            $result['latitude'] = ($coords['latMin'] + $coords['latMax']) / 2;
                            $result['longitude'] = ($coords['lonMin'] + $coords['lonMax']) / 2;

                            break;
                        }
                    }
                }
            }

            $Text = new \Categoryzator\Core\Text();
            $Text -> addContent($result['name'])
                  -> addContent($result['description'])
                  -> returnTag(true);

            $categoryzator = new \Categoryzator\Categoryzator($Text);
            $newText = $categoryzator->analiz(\Categoryzator\Categoryzator::MULTI_CATEGORY);
            $cats = array();

            foreach ($newText->category as $key => $c) {
                $cat = \Models\Category::findFirst("key = '".$c."'");
                $cats[$key] = new \Models\EventCategory();
                $cats[$key]->category_id = $cat->id;
            }
            $result['event_category'] = $cats;

            $eventObj = new \Models\Event();
            $eventObj -> assign($result);

            if ($eventObj -> save()) {
                if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
                    $ch =  curl_init($ev['pic_big']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $content = curl_exec($ch);
                    if ($content) {
                        if (!is_dir($this -> config -> application -> uploadDir . 'img/event/' . $eventObj->id)) {
                            mkdir($this -> config -> application -> uploadDir . 'img/event/' . $eventObj->id);
                        }
                        $fPath = $this -> config -> application -> uploadDir . 'img/event/' . $eventObj->id.'/'.$logo;
                        $f = fopen($fPath, 'wb');
                        fwrite($f, $content);
                        fclose($f);
                        chmod($fPath, 0777);
                    }
                }

                $images = new \Models\EventImage();
                $images -> assign(array(
                        'event_id' => $eventObj -> id,
                        'image' => $ev['pic_big']
                    ));
                $images -> save();
                $this -> cacheData -> save('fbe_' . $ev['eid'], $eventObj -> id);
                $newEvents[$eventObj -> id] = $eventObj -> fb_uid;
            }
        } 

        if ($this -> cacheData -> exists('fbe_' . $ev['eid']) && isset($ev['venue'])){ 
            $newEvents[$this -> cacheData -> get('fbe_' . $ev['eid'])] = $ev['eid'];
        } 

        if (!empty($newEvents)) {
        	switch ($msg['type']) {
        		case 'friend_going_event':
        				foreach ($newEvents as $id => $ev) {
                            if (!$this -> cacheData -> exists('member.friends.go.' . $msg['args'][2] . '.' . $id)) {
                                $events = array('member_id' => $msg['args'][2],
                                   			 	'event_id' => $id);
                                $obj = new \Models\EventMemberFriend();
                                $obj -> assign($events);
                                $obj -> save();
                                $this-> cacheData -> save('member.friends.go.' . $msg['args'][2] . '.' . $id, $ev);
                            }
                        }
        			break;

        		case 'user_going_event':
        				foreach ($newEvents as $id => $ev) {
        					if (!$this -> cacheData -> exists('member.go.' . $msg['args'][2] . '.' . $id)) {
                                $events = array('member_id' => $msg['args'][2],
				                                'event_id' => $id,
				                                'member_status' => 1);
                                $obj = new \Models\EventMember();
                                $obj -> assign($events);
                                $obj -> save();
                                $this -> cacheData -> save('member.go.' . $msg['args'][2] . '.' . $id, $id);
                            }
                        }
        			break;

        		case 'page_event':
        				foreach ($newEvents as $id => $ev) {
	                        if (!$this -> cacheData -> exists('member.like.' . $msg['args'][2] . '.' . $id)) {
	                            $newData = array('member_id' => $msg['args'][2],
                                                 'event_id' => $id,
                                                 'status' => 1);
	                            $obj = new \Models\EventLike();
	                            $obj -> assign($newData);
	                            $obj -> save();
	                            $this -> cacheData->save('member.like.' . $msg['args'][2] . '.' . $id, $id);
	                        }
	                    }
        			break;
        	}
        } 
	}
}
