<?php

namespace Jobs\Grabber\Categorize;


class Categorize
{
	public $di;

	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}


	public function run()
	{
		$shards = (new \Models\Event()) -> getAvailableShards();

		foreach ($shards as $cri) {
			$shard = (new \Models\Event()) -> setShard($cri);
				
			
		}
	}
}