<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag,
	Models\Tag,
	Models\Total,
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
		
			$result = array();

			$result['eb_uid'] = $ev['id'];
			$result['eb_url'] = $ev['url'];
			$result['description'] = preg_replace('/<a[^>]*>((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.#?=-]*)*\/?)<\/a>/ui', '<a href="$1" target="_blank">$1</a>', $ev['description']['text']);
			$result['name'] = $ev['name']['text'];
			$result['location_id'] = '0';
			
			if (isset($ev['logo_url']) && !empty($ev['logo_url'])) {
                $ext = explode('.', $ev['logo_url']);
                $logo = 'eb_' . $ev['id'] . '.' . end($ext);
                $result['logo'] = $logo;
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
			$result = $this -> processDates($result);
            
            if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude'])) {
               	$result['latitude'] = $ev['venue']['latitude'];
		        $result['longitude'] = $ev['venue']['longitude'];
		        
				$locations = new \Models\Location();
				$locExists = $locations -> createOnChange(['latitude' => $ev['venue']['latitude'], 'longitude' => $ev['venue']['longitude']]);
						
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
            }

            if (isset($ev['venue']['latitude']) && isset($ev['venue']['longitude']) && isset($ev['venue']['id'])) {
                $venueObj = new \Models\Venue();
                isset($ev['venue']['name']) ? $venueName = $ev['venue']['name'] : $venueName = '';
                $venueObj -> assign([
					'eb_uid' => $ev['venue']['id'],
                    'location_id' => $result['location_id'],
                    'name' => $venueName,
                    'address' => $result['address'],
                    'latitude' => $ev['venue']['latitude'],
                    'longitude' => $ev['venue']['longitude']]);

                if ($venueObj -> save() != false) {
                        $venueCreated = $venueObj;
                        $this -> cacheData -> save($this -> ebUidCachePrefix . 'venue_' . $venueObj -> eb_uid, 
                                                array('venue_id' => $venueObj -> id,
                                                      'address' => $venueObj -> address,
                                                      'location_id' => $venueObj -> location_id,
                                                      'latitude' => $venueObj->latitude,
                                                      'longitude' => $venueObj->longitude));
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
			
	        $eventObj = (new \Models\Event())-> setShardByCriteria($result['location_id']);
	        $eventObj -> assign($result);
	        
			if ($eventObj -> save() != false) {
				$this -> categorize($eventObj);
				
            	$total = Total::findFirst('entity = "event"');
            	$total -> total = $total -> total + 1;
            	$total -> update();

				if (isset($ev['logo']) && !empty($ev['logo'])) {
                    $this -> saveEventImage('eb', $ev['logo']['url'], $eventObj);
                }

                $grid = new \Models\Event\Grid\Search\Event(['location' => $result['location_id']], $this->_di, null, ['adapter' => 'dbMaster']);
                $indexer = new \Models\Event\Search\Indexer($grid);
                $indexer->setDi($this->_di);
                $indexer->addData($eventObj -> id);
			}
		}
}
