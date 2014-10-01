<?php

namespace Vendor\Eventbrite;

use Vendor\Eventbrite\Base,
	Models\Eventbrite as Ebrite,
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
	
	public function getEventsByCity($city, $lastId)
	{
		$result = $this -> setTokenType(parent::TOKEN_TYPE_PERSONAL)
			  			-> setEntity('events')
			  			-> setFilter('venue.city', $city)
			  			-> setFilter('since_id', $lastId)
			  			-> getData();
		return $result;
	}
	
	public function getEventDetails()
	{
		
	}
	
	public function getUserDetails()
	{
		
	}
}