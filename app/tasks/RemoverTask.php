<?php

namespace Tasks;

class RemoverTask extends \Phalcon\CLI\Task
{
	public function removeLocationsAction()
	{
		$job = new \Jobs\Grabber\Clean\Locations($this -> getDi());
		$job -> run();
	}
	
	
	public function removeEventsAction()
	{
		$job = new \Jobs\Grabber\Clean\Events($this -> getDi());
		$job -> runEb();
	}
	
	
	public function removeEventsIncorrectLocationsAction()
	{
		$job = new \Jobs\Grabber\Clean\Events($this -> getDi());
		$job -> runIncorrectLocations();
	}
	
	
	public function removeEventsPastImagesAction()
	{
		$job = new \Jobs\Grabber\Clean\EventImages($this -> getDi());
		$job -> run();
	}
}