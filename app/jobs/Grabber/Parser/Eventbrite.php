<?php

namespace Jobs\Grabber\Parser;

use Models\Venue,
	Models\Event;

class Eventbrite
{
	use \Jobs\Grabber\Parser\Helper;
	
	public $cacheData;
	private $ebUidCachePrefix = 'ebUid';
    private $_di;
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
        $this -> config = $dependencyInjector -> get('config');
        
        if (isset($this -> config -> cache -> prefixes -> ebUid)) {
        	$this -> ebUidCachePrefix = $this -> config -> cache -> prefixes -> ebUid;
        }

        $this->_di = $dependencyInjector;
	}
	
	
	public function run(\AMQPEnvelope $data)
	{
		error_reporting(E_ALL & ~E_NOTICE);
		
		$msg = unserialize($data -> getBody());
		$ev = $msg['item'];
		$eventObj = (new \Models\Event()) -> existsInShardsBySourceId($ev['id'], 'eb');
		
		if (!$eventObj) {
			$result = array();
print_r($ev['name']['text'] . "\n\r");

			$result['eb_uid'] = $ev['id'];
			$result['eb_url'] = preg_replace('/<a[^>]*>((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.#?=-]*)*\/?)<\/a>/ui', '<a href="$1" target="_blank">$1</a>', $ev['url']);
			$result['deleted'] = "0";
			$result['description'] = preg_replace('/<a[^>]*>((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.#?=-]*)*\/?)<\/a>/ui', '<a href="$1" target="_blank">$1</a>', $ev['description']['text']);
			$result['name'] = $ev['name']['text'];
			if (isset($ev['location_id']) && !empty($ev['location_id'])) {
				$result['location_id'] = $ev['location_id'];
			} else {
				$result['location_id'] = '0';
			}
			
            if (isset($ev['logo']) && isset($ev['logo']['url']) && !empty($ev['logo']['url'])) {
            	$info = getimagesize($ev['logo']['url']);
            	$logoExt = image_type_to_extension($info[2]);
            	$logo = 'eb_' . $ev['id'] . $logoExt;
            	$result['logo'] = $logo;
            }

            if (isset($ev['start']) && !empty($ev['start'])) {
            	$ev['start_time'] = $ev['start']['local'];
            }
            if (isset($ev['end']) && !empty($ev['end'])) {
            	$ev['end_time'] = $ev['end']['local'];
            }
            $result = $this -> processDates($result, $ev);

            if (isset($ev['venue']['id']) && $venue = Venue::findFirst(['eb_uid = "' . $ev['venue']['id'] . '"'])) {
            	$result['venue_id'] = $venue -> id;
            	$result['address'] = $venue -> address;
            	$result['latitude'] = $venue -> latitude;
            	$result['longitude'] = $venue -> longitude;
            	$result['location_id'] = $venue -> location_id;
            	
            } else {
            	if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude'])) {
	               	$result['latitude'] = $ev['venue']['latitude'];
			        $result['longitude'] = $ev['venue']['longitude'];
			        
					$locations = new \Models\Location();
					$locExists = $locations -> createOnChange($ev['venue']);
							
					if ($locExists) {
			          	$result['location_id'] = $locExists -> id;
			                		
			           	if (isset($ev['venue']['address'])) {
			        		$result['address'] = $ev['venue']['address']['address_1'];
				    	} elseif(!empty($ev['venue']['name']))  {
			          		$result['address'] = $ev['venue']['name'];
			        	} else {
			        		$result['address'] = '';
			        	}
					}
					
					if (isset($ev['venue']['id'])) {
						$venueObj = new \Models\Venue();
						isset($ev['venue']['name']) ? $venueName = $ev['venue']['name'] : $venueName = '';
						$venueObj -> assign([
								'eb_uid' => $ev['venue']['id'],
								'location_id' => $result['location_id'],
								'name' => $venueName,
								'address' => $result['address'],
								'latitude' => $ev['venue']['latitude'],
								'longitude' => $ev['venue']['longitude']]);
					
						if ($venueObj -> save()) {
							$result['venue_id'] = $venueObj -> id;
						} 
					}	
	            }
            }

            $eventObj = (new \Models\Event())-> setShardByCriteria($result['location_id']);
	        $eventObj -> assign($result);
	        
			if ($eventObj -> save() != false) {
				$this -> categorize($eventObj);

				if (isset($ev['logo']) && !empty($ev['logo'])) {
                    $this -> saveEventImage('eb', $ev['logo']['url'], $eventObj);
                }
				$this -> addToIndex($eventObj);
			}
		} else {
			$result = [];
			if (isset($ev['start']) && !empty($ev['start'])) {
				$ev['start_time'] = $ev['start']['local'];
			}
			if (isset($ev['end']) && !empty($ev['end'])) {
				$ev['end_time'] = $ev['end']['local'];
			}
			$result = $this -> processDates($result, $ev);

			if (!empty($result)) {
				foreach ($result as $field => $val) {
					$eventObj -> $field = $val;
				}
				$eventObj -> setShardById($eventObj -> id);
				if (!$eventObj -> update()) {
					print_r($eventObj -> id . ": ooops, dates not updated\n\r");
				}
				$this -> addToIndex($eventObj);
			}
		}
	}
}
