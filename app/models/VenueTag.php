<?php

namespace Models;

class VenueTag extends \Library\Model
{
    public $id;
    public $venue_id;
    public $tag_id = 1;

    
    public function initialize()
    {
    	parent::initialize();    	
    	
        $this->belongsTo('venue_id', '\Models\Venue', 'id', array('alias' => 'venue_tag'));
        $this->belongsTo('tag_id', '\Models\Tag', 'id', array('alias' => 'venue_tag'));
    }
}