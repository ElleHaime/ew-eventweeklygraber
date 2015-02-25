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
		if (!$this -> cacheData -> exists('fb_venues')) {
			$venue = new \Models\Venue();
			$venue -> setCache();
		}
		if (!$this -> cacheData -> exists('fb_events')) {
			$event = new \Models\Event();
			$event -> setCache();
		}
	}
}