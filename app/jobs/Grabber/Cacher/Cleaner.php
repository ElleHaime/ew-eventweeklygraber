<?php

namespace Jobs\Grabber\Cacher;

class Cleaner
{
    public $cacheData;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> cacheData = $dependencyInjector -> get('cacheData');
	}
	
	public function run($memberId)
	{
		print_r("Cache cleaned for member #" . $memberId . "\n\r");		
	}
}