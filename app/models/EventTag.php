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
    	
        $this->belongsTo('event_id', '\Objects\Event', 'id', array('alias' => 'event_tag'));
        $this->belongsTo('tag_id', '\Objects\Tag', 'id', array('alias' => 'event_tag'));
    }
    
    
    public function deleteEventTag($eventObject)
    {
		$tags = $eventObject -> event_tag;
		
		if ($tags) {
			foreach ($tags as $tag) {
				$tag -> setShardById($eventObject -> id) -> delete();
			} 
		} 
    } 
}