<?php

namespace Jobs\Parser;

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
		$msg = unserialize($data -> getBody());
		$ev = $msg['item'];
		$locationsScope = $this -> cacheData -> get('locations');

		if (!$this -> cacheData -> exists('fbe_' . $ev['eid'])) 
        {
            $result = array();
            $result['fb_uid'] = $ev['eid'];
            $result['fb_creator_uid'] = $ev['creator'];
            $result['description'] = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $ev['description']);
            $result['name'] = $ev['name'];
            $result['address'] = '';

            if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
                $ext = explode('.', $ev['pic_big']);
                if (strpos(end($ext), '?')) {
                    $logo = 'fb_' . $ev['eid'] . '.' . substr(end($ext), 0, strpos(end($ext), '?'));
                } else {
                    $logo = 'fb_' . $ev['eid'] . '.' . end($ext);
                }
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
                    if (!empty($locationsScope)) {
                        foreach ($locationsScope as $loc_id => $coords) {
                            if ($ev['venue']['latitude'] >= $coords['latMin'] && $coords['latMax'] >= $ev['venue']['latitude'] &&
                                $ev['venue']['longitude'] <= $coords['lonMax'] && $coords['lonMin'] <= $ev['venue']['longitude'])
                            {
                                $result['location_id'] = $loc_id;

                                if (isset($ev['venue']['street'])) {
                                    $result['latitude'] = $ev['venue']['latitude'];
                                    $result['longitude'] = $ev['venue']['longitude'];
                                    $result['address'] = $ev['venue']['street'];
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


                if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude']) && isset($ev['venue']['id'])) {
                    $venueObj = new \Models\Venue();
                    isset($ev['location']) ? $venueName = $ev['location'] : '';
                    $venueObj -> assign(array(
                            'fb_uid' => $ev['venue']['id'],
                            'location_id' => $result['location_id'],
                            'name' => $venueName,
                            'address' => $ev['venue']['street'],
                            'latitude' => $ev['venue']['latitude'],
                            'longitude' => $ev['venue']['longitude']
                    ));

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
                    if (isset($ev['venue']['name'])) {
                        $result['address'] = $ev['venue']['name'];
                    }
                }
            }
print_r($result);
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

            if ($eventObj -> save() != false) {
                if (isset($ev['pic_big']) && !empty($ev['pic_big'])) {
                    $this -> saveEventImage($ev['pic_big'], $eventObj);
                }
                if (isset($ev['pic_cover']) && !empty($ev['pic_cover'])) {
                    $this -> saveEventImage($ev['pic_cover']['source'], $eventObj, 'cover');
                }

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

                case 'user_page_event':
                        foreach ($newEvents as $id => $ev) {
                            $obj = \Models\Event::findFirst('id = ' . $id);
                            $obj -> member_id = $msg['args'][2];
                            $obj -> update();
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
            $fDir = $this -> config -> application -> uploadDir . 'img/event/' . $event -> id;
            $fPath = $this -> config -> application -> uploadDir . 'img/event/' . $event -> id . '/' . $img;
        } else {
            $fDir = $this -> config -> application -> uploadDir . 'img/event/' . $event -> id . '/' . $imgType;
            $fPath = $this -> config -> application -> uploadDir . 'img/event/' . $event -> id . '/' . $imgType . '/' . $img;            
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