<?php

namespace Jobs\Application\Cacher;

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
				
		print_r("Data cached: total\n\r");		
	}
}