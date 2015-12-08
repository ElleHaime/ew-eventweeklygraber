<?php

namespace Jobs\Seo;

use Models\Event,
	Models\Cron,
	Models\Location;

class Generator
{
	protected $di;
	
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> _config = $dependencyInjector -> get('config');
		$this -> _di = $dependencyInjector;
	}
	
	
	public function run()
	{
		$locations = Location::find();
		 		
		foreach($locations as $loc)
		{
			
		}
	}
}