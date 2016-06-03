<?php 

namespace Models;

class VenueCategory extends \Library\Model
{
	public $id;
	public $venue_id;
	public $category_id = 6; 
	
	public function initialize()
	{
		parent::initialize();
		
        $this -> belongsTo('venue_id', '\Models\Venue', 'id', array('alias' => 'venue_category'));
        $this -> belongsTo('category_id', '\Models\Category', 'id', array('alias' => 'venuepart2'));
	}
}