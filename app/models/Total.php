<?php

namespace Models;

class Total extends \Library\Model
{
    public $id;
    public $entity;
    public $total;

    public function setCache()
    {
    	$evTotal = self::findFirst('entity = "event"');
    	$this -> cacheData -> save('eventsGTotal', $evTotal -> total);
    }
}