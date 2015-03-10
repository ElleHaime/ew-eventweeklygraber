<?php

namespace Models;

class Tag extends \Library\Model
{
    public $id;
    public $key;
    public $name;
    public $category_id;

    public function initialize()
    {
    	parent::initialize();    	
    	
        $this->hasMany('id', '\Objects\EventTag', 'event_id', array('alias' => 'event_tag'));
    }
}