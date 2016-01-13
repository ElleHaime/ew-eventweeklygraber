<?php 

namespace Models;

class EventImage extends \Library\Model
{
    use \Sharding\Core\Env\Phalcon;	
	
	public $id;
	public $event_id;
	public $image;
	public $type;

	public function initialize()
	{
		parent::initialize();		
		
		$this -> belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'event'));
	}
	
	
	public function beforeDelete()
	{
		$imgPath = $this -> getDi() -> get('config') -> application -> uploadDir . $this -> event_id . '/' . $this -> type . '/' . $this -> image;
		if (file_exists($imgPath)) {
print_r($imgPath . "\n\r");			
			unlink($imgPath);			
		}
	}
}