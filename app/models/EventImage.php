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
	
	
	public function transferBetweenShards($relationName, $oldObject, $parentId)
	{
		$uploadDir = $this -> getDi() -> get('config') -> application -> uploadDir;
		if (is_dir($uploadDir . $oldObject -> id)) rename($uploadDir . $oldObject -> id, $uploadDir . $parentId);
		
		parent::transferBetweenShards($relationName, $oldObject, $parentId);
	}
}