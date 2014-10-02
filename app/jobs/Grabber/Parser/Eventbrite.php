<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag,
	Models\Tag,
	Models\Event;

class Eventbrite
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
print_r($ev);
die();		
		if (!Event::findFirst('eb_uid = "' . $ev['id'] . '"'))
		{
			$result = array();

			$result['eb_uid'] = $ev['id'];
			$result['eb_url'] = $ev['url'];
			$result['description'] = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $ev['description']);
			$result['name'] = $ev['name']['text'];
			
			if (isset($ev['logo_url']) && !empty($ev['logo_url'])) {
                $ext = explode('.', $ev['logo_url']);
                $logo = 'eb_' . $ev['id'] . '.' . end($ext);
                $result['logo'] = $logo;
            }
            
			if (!empty($ev['ticket_classes'])) {
				
			}

			if(!empty($ev['start'])) {
                $start = explode('T', $ev['start']['local']);
                $result['start_date'] = $start[0];
                if (isset($start[1])) {
                    $result['start_time'] = $start[1]; 
                }
            }
            
			if(!empty($ev['end'])) {
                $end = explode('T', $ev['end']['local']);
                $result['end_date'] = $end[0];
                if (isset($end[1])) {
                    $result['end_time'] = $end[1]; 
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
            
            
            if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude'])) {
            	$result['latitude'] = $ev['venue']['latitude'];
                $result['longitude'] = $ev['venue']['longitude'];
				$locations = new \Models\Location();
				$locExists = $locations -> createOnChange(['latitude' => $ev['venue']['latitude'], 'longitude' => $ev['venue']['longitude']]);
                	
				if ($locExists) {
                	$result['location_id'] = $locExists -> id;
                		
                	if (isset($ev['venue']['address'])) {
               			$result['address'] = $ev['venue']['address_1'];
	           		} elseif(!empty($ev['venue']['name']))  {
                		$result['address'] = $ev['venue']['name'];
               		} else {
               			$result['address'] = '';
               		}
				}
            }

            if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude']) && isset($ev['venue']['id'])) {
                $venueObj = new \Models\Venue();
                isset($ev['venue']['name']) ? $venueName = $ev['venue']['name'] : $venueName = '';
                $venueObj -> assign([
					'eb_uid' => $ev['venue']['id'],
                	'eb_url' => $ev['venue']['recource_uri'],
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
print_r($result);
die();
			
		}
print_r("done\n\r");		
die();		
	}
	
	
	protected function processDates()
	{
	}
	
}
