<?php

namespace Models;

class EventTag extends \Phalcon\Mvc\Model
{
    public $id;

    public $event_id;

    public $tag_id = 1;

    public function initialize()
    {
        $this->belongsTo('event_id', '\Objects\Event', 'id', array('alias' => 'event_tag'));
        $this->belongsTo('tag_id', '\Objects\Tag', 'id', array('alias' => 'event_tag'));
    }
}