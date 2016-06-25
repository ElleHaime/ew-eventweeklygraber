<?php

namespace Models;

class EventTag extends \Library\Model
{
    use \Sharding\Core\Env\Phalcon;	
	
    public $id;
    public $event_id;
    public $tag_id = 1;

    
    public function initialize()
    {
    	parent::initialize();    	
    	
        $this->belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event_tag'));
        $this->belongsTo('tag_id', '\Models\Tag', 'id', array('alias' => 'event_tag'));
    }
    
    
    public function deleteEventTag($eventObject)
    {
    	$tags = $this -> setShardById($eventObject -> id) 
    				  -> strictSqlQuery()
					  -> addQueryCondition('event_id = "' . $eventObject -> id . '"')
					  -> addQueryFetchStyle('\Models\EventTag')
    				  -> selectRecords();
  	
    	if (!empty($tags)) {
    		foreach ($tags as $tag) {
    			$tag -> setShardById($tag -> event_id) -> delete(); 
    		}
    	}

		return;
    } 
}