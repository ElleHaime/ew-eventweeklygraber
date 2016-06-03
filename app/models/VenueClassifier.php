<?php 

namespace Models;

class VenueClassifier extends \Library\Model
{
	public $id;
	public $venue_id;
	public $classifier_id = 6; 
	
	public function initialize()
	{
		parent::initialize();
		
        $this -> belongsTo('venue_id', '\Models\Venue', 'id', array('alias' => 'venue_classifier'));
        $this -> belongsTo('classifier_id', '\Models\Classifier', 'id', array('alias' => 'venuepart3'));	
	}
}