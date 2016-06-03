<?php

namespace Jobs\Grabber\Parser;

use Models\VenueTag,
	Models\VenueCategory,
	Models\VenueClassifier,
	Models\Tag,
	Models\Venue,
	Models\Cron,
	Models\Venue,
	Models\Location,
	Models\Classifier;

class FacebookVenue
{
	use \Jobs\Grabber\Parser\Helper;
		
	protected $_di;


	public function __construct(\Phalcon\DI $dependencyInjector)
	{
        $this -> config = $dependencyInjector -> get('config');
        $this->_di = $dependencyInjector;
	}

	
	public function run(\AMQPEnvelope $data)
	{
		$msg = unserialize($data -> getBody());
		$venue = $msg['item'];
		
print_r($venue); die();
		
		die();
	}
}