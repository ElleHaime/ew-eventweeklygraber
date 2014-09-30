<?php

namespace Vendor\Eventbrite;

use Vendor\Eventbrite\Base,
	\Exception as Exception;

class Eventbrite extends Base
{

	public function __construct($dependencyInjector = null)
	{
		if (!is_null($dependencyInjector)) {
            $appCfg = $dependencyInjector -> get('config');
            parent::__construct($appCfg -> eventbrite);
		} else {
            throw new Exception('Oooops, something went wrong. With love, you EventbriteAPI');
        }
	}
	
	public function getCategories()
	{
		$result = $this -> setTokenType(parent::TOKEN_TYPE_PERSONAL)
			  			-> setEntity('subcategories')
			  			-> makeRequest();
print_r($result);
die();			  			
		return $result;
	}
	
	public function getEventsByCity($arg)
	{
		$result = $this -> setTokenType(parent::TOKEN_TYPE_PERSONAL)
			  			-> setEntity('events')
			  			-> setFilter('venue.city', $arg)
			  			-> makeRequest();
print_r($result);
die();			  			
	}
	
	public function getEventDetails()
	{
		
	}
	
	public function getUserDetails()
	{
		
	}
}