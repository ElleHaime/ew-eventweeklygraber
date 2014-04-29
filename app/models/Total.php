<?php

namespace Models;

class Total extends \Phalcon\Mvc\Model
{
    public $id;
    public $entity;
    public $total;

    public function initialize()
    {
		parent::initialize();
    }
    
    public function setCache()
    {
    	$evTotal = self::findFirst('entity = "event"');
    	$this -> cacheData -> save('eventsGTotal', $evTotal -> total);
    }
}