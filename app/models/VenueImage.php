<?php 

namespace Models;

class VenueImage extends \Library\Model
{
	public $id;
	public $venue_id;
	public $image;
	public $type;

	public function initialize()
	{
		parent::initialize();		
		
		$this -> belongsTo('venue_id', '\Models\Venue', 'id', array('alias' => 'venue'));
	}
	
	
	public function beforeDelete()
	{
		$imgPath = $this -> getDi() -> get('config') -> application -> uploadDir -> venue . $this -> venue_id . '/' . $this -> type . '/' . $this -> image;
		if (file_exists($imgPath)) {
print_r($imgPath . "\n\r");			
			unlink($imgPath);			
		}
	}
}