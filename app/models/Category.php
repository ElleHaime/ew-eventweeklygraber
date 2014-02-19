<?php 

namespace Models;

class Category extends \Phalcon\Mvc\Model
{
	public $id;
	public $key;
	public $name;
    public $parent_id;
    public $is_default;
	
	public function initialize()
	{
		$this -> hasMany('id', '\Models\EventCategory', 'category_id', array('alias' => 'eventpart'));
	}
}