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
			  			-> setEntityId('search')
			  			-> setFilter('venue.city', $city)
			  			-> setFilter('since_id', $lastId)
			  			-> setFilter('sort_by', 'id')
			  			-> setFilter('start_date.range_start', date('Y-m-d\TH:i:s\Z'))
			  			-> getData();
		return $result;
	}
	
	public function getVenueById($id)
	{
		$this -> clearFilters();
		
		$result = $this -> setTokenType(parent::TOKEN_TYPE_PERSONAL)
						-> setEntity('venues')
						-> setEntityId($id)
						-> setPagination(false)
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