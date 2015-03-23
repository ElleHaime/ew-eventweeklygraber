<?php

namespace Models;

class Page extends \Library\Model
{
	public $id;
	public $fb_uid;
	public $fb_uname;
	public $name;
	public $link;
	public $category;
	public $description;
	public $phone;
	public $site;
	public $location_id;
	public $likes;
	
	
	public function initialize()
	{
		parent::initialize();
	}
}