<?php

namespace Jobs\Grabber\Clean;

class Venues
{
	public $di;
	protected $batchSize = 200;
	
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function run()
	{
		
	}
}