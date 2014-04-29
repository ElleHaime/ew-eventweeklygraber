<?php

namespace Jobs\Grabber\Cacher;

class Totalcache
{
    public $cacheData;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
	}
	
	public function run()
	{
		if (!$this -> cacheData -> exists('locations')) {
			$location = new \Models\Location();
			$location -> setCache();
		}
		if (!$this -> cacheData -> exists('fb_venues')) {
			$venue = new \Models\Venue();
			$venue -> setCache();
		}
		if (!$this -> cacheData -> exists('fb_events')) {
			$event = new \Models\Event();
			$event -> setCache();
		}
		if (!$this -> cacheData -> exists('fb_members')) {
			$memberNetwork = new \Models\MemberNetwork();
			$memberNetwork -> setCache();
		}
		if (!$this -> cacheData -> exists('eventsGTotal')) {
			$total = new \Models\Total();
			$event -> setCache();
		}
				
		print_r("Data cached: total\n\r");		
	}
}