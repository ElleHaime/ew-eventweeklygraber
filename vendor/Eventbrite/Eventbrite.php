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
			  			-> setEntity('categories')
			  			-> getData();
		return $result;
	}
	
	public function getSubcategories()
	{
		$result = $this -> setTokenType(parent::TOKEN_TYPE_PERSONAL)
			  			-> setEntity('subcategories')
			  			-> getData();
		return $result;
	}
	
	public function getEventsByCity($arg)
	{
		$result = $this -> setTokenType(parent::TOKEN_TYPE_PERSONAL)
			  			-> setEntity('events/search')
			  			-> setFilter('venue.city', $arg)
			  			-> getData();
	}
	
	public function getEventDetails()
	{
		
	}
	
	public function getUserDetails()
	{
		
	}
}