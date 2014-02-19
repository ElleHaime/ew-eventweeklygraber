<?php 

namespace Models;

class EventCategory extends \Phalcon\Mvc\Model
{
	public $id;
	public $event_id;
	public $category_id = 1; 
	
	public function initialize()
	{
        $this -> belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event_category'));
        $this -> belongsTo('category_id', '\Models\Category', 'id', array('alias' => 'eventpart2'));
	}
}