<?php 

namespace Models;

class EventCategory extends \Library\Model
{
    use \Sharding\Core\Env\Phalcon;	
	
	public $id;
	public $event_id;
	public $category_id = 1; 
	
	public function initialize()
	{
		parent::initialize();
		
        $this -> belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event_category'));
        $this -> belongsTo('category_id', '\Models\Category', 'id', array('alias' => 'eventpart2'));
	}
	
	public function deleteEventCategory($eventObject)
	{
    	$categories = $this -> setShardById($eventObject -> id) 
    				  		-> strictSqlQuery()
							-> addQueryCondition('event_id = "' . $eventObject -> id . '"')
							-> addQueryFetchStyle('\Models\EventCategory')
		    				-> selectRecords();
  	
    	if (!empty($categories)) {
    		foreach ($categories as $ct) {
    			$ct -> setShardById($ct -> event_id) -> delete(); 
    		}
    	}
		
		return;
	}
	
}