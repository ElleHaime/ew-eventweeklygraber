<?php

namespace Models;

class Event extends \Library\Model
{
    use \Sharding\Core\Env\Phalcon;
	
	public $id;
	public $fb_uid;
	public $eb_uid;
	public $eb_url;
	public $fb_creator_uid;
	public $member_id;
	public $campaign_id;
	public $location_id;
	public $venue_id;
	public $name;
	public $description;
    public $tickets_url;
	public $start_date;
	public $end_date;
	public $recurring;
	public $event_status	= 1;
    public $event_fb_status	= 1;
	public $latitude;
	public $longitude;
	public $address;
	public $logo;
	public $is_description_full = 0;
    public $deleted = 0;


	public function initialize()
	{
		parent::initialize();		
		
		$this -> belongsTo('venue_id', '\Models\Venue', 'id', array('alias' => 'venue',
																	 'baseField' => 'name'));
		$this -> belongsTo('location_id', '\Models\Location', 'id', array('alias' => 'location',
																	 	   'baseField' => 'alias'));
		$this -> hasMany('id', '\Models\EventImage', 'event_id', array('alias' => 'image'));
		$this -> hasMany('id', '\Models\EventMember', 'event_id', array('alias' => 'memberpart'));
		$this -> hasMany('id', '\Models\EventMemberFriend', 'event_id', array('alias' => 'memberfriendpart'));
		$this -> hasMany('id', '\Models\EventLike', 'event_id', array('alias' => 'memberlike'));
		$this -> hasMany('id', '\Models\EventCategory', 'event_id', array('alias' => 'event_category'));
        $this -> hasMany('id', '\Models\EventTag', 'event_id', array('alias' => 'event_tag'));
        $this -> hasOne('id', '\Models\EventRating', 'event_id', array('alias' => 'event_rating'));
        $this -> hasOne('id', '\Models\Featured', 'object_id', array('alias' => 'event_featured'));
	}
	
	
	public function getAllByParams($args = [])
	{
		if (empty($args)) {
			throw new \Exception('Arguments can\'t be empty');
		}
		$result = [];
		
		$shards = $this -> getAvailableShards();
		foreach ($shards as $cri) {
			$events = (new \Models\Event()) -> setShard($cri);
			
			$query = implode(' AND ', $args);
			$eventExists = $events -> strictSqlQuery()
								   -> addQueryCondition($query)
								   -> addQueryFetchStyle('\Models\Event')
								   -> selectRecords();
			
			if (!empty($eventExists)) {
				foreach ($eventExists as $eventObj) {
					$result[] = $eventObj -> eb_uid;
				}
			}
		}
		
		return $result;
	}
	
	
	public function existsInShardsBySourceId($id, $source = 'fb')
	{
		$shards = $this -> getAvailableShards();
		$result = false;
		
		foreach ($shards as $cri) {
			$events = (new \Models\Event()) -> setShard($cri);
			$eventExists = $events -> strictSqlQuery()
								   -> addQueryCondition($source . '_uid="' . $id . '"')
								   -> addQueryFetchStyle('\Models\Event')
								   -> selectRecords();
	
			if (!empty($eventExists)) {
				foreach ($eventExists as $eventObj) {
					return $eventObj;
				}
			}
		}
		
		return $result;
	}
	
	
	public function getNumByCriteria($criteriaId)
	{
		$eNumber = 0;
		
		$events = (new \Models\Event()) -> setShardByCriteria($criteriaId);
		$eventExists = $events -> strictSqlQuery()
								-> addQueryCondition('location_id=' . $criteriaId)
								-> selectCount();
		if ($eventExists) $eNumber = $eventExists;
		
		return $eNumber;
	}
	
	
	public function archivePhalc($needArchive = true)
	{
		// move: event_image, event_site, event_tag, event_category
		$ready = true;
		
		if ($needArchive) {
			$archive = new \Models\EventArchive();
			$archive -> assign(['event' => serialize($this),
								'archived' => date('Y-m-d H:i:s')]);
			$ready = $archive -> save();
		} 
		
		if ($ready) {
			if ($this -> memberlike) {
				$this -> memberlike -> delete();
			}
			if ($this -> memberpart) {
				$this -> memberpart -> delete();
			}
			if ($this -> memberfriendpart) {
				$this -> memberfriendpart -> delete();
			}
			if ($this -> event_rating) {
				$this -> event_rating -> delete();
			}
			if ($this -> image) {
				$this -> image -> delete();
			}
			if ($this -> event_tag) {
				$this -> event_tag -> delete();
			}
			if ($this -> event_category) {
				$this -> event_category -> delete();
			}
			(new \Models\Featured) -> deleteEventFeatured($this -> id);
				
			$this -> delete();
		}
	
		return;
	}
	
	
	public static function checkExpirationDate($date)
	{
		if (strtotime($date) > time()) {
			return true;
		} else {
			return false;
		}
	}
	
	
	public function transferEventBetweenShards($eventObj, $criteria)
	{
		$eventObjNew = clone $eventObj;
		unset($eventObjNew -> id);
		
		$eventObjNew -> location_id = $criteria;
		$eventObjNew -> setShardByCriteria($criteria);
		
		if (!$eventObjNew -> save()) {
			print_r($ev['fb_creator_uid'] . ": ooops, location not updated\n\r");
			
			return false;
		}
print_r("\n\r... saved to new shard...\n\r");
		(new \Models\EventImage()) -> transferBetweenShards('image', $eventObj, $eventObjNew -> id);
print_r("... save image...\n\r");		
		(new \Models\EventTag()) -> transferBetweenShards('event_tag', $eventObj, $eventObjNew -> id);
print_r("... save tag...\n\r");		
		(new \Models\EventCategory()) -> transferBetweenShards('event_category', $eventObj, $eventObjNew -> id);
print_r("... save category...\n\r");		
		(new \Models\EventMember()) -> transferInShards('memberpart', $eventObj, $eventObjNew -> id);
print_r("... save memberpart...\n\r");
		(new \Models\EventMemberFriend()) -> transferInShards('memberfriendpart', $eventObj, $eventObjNew -> id);
print_r("... save memberfrindpart...\n\r");		
		(new \Models\EventLike()) -> transferInShards('memberlike', $eventObj, $eventObjNew -> id);
print_r("... save memberlike...\n\r");
		(new \Models\EventRating()) -> transferInShards('event_rating', $eventObj, $eventObjNew -> id);
print_r("... save rating...\n\r");		
		(new \Models\Featured()) -> transferInShards(\Models\Featured::EVENT_OBJECT_TYPE, $eventObj, $eventObjNew -> id);
print_r("... save featured...\n\r");

		$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObjNew -> location_id], $this -> getDi(), null, ['adapter' => 'dbMaster']);
		$indexer = new \Models\Event\Search\Indexer($grid);
		$indexer -> setDi($this -> getDi());
		if (!$indexer -> addData($eventObjNew -> id)) {
			print_r("ooooooops, did not added to index\n\r");
		}
		
		$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> getDi(), null, ['adapter' => 'dbMaster']);
		$indexer = new \Models\Event\Search\Indexer($grid);
		$indexer -> setDi($this -> getDi());
		if (!$indexer -> deleteData($eventObj -> id)) {
			print_r("ooooooops, wasn't deleted from index\n\r");
		}
		
		$eventObj -> setShardById($eventObj -> id);
		$eventObj -> delete();
print_r("... old events removed ...\n\r");		
		return $eventObjNew;
	}


	public function setCache()
	{
	}
	
	
	public function beforeDelete()
	{
		$imgPath = $this -> getDi() -> get('config') -> application -> uploadDir . $this -> id;
		
		if (!is_dir($imgPath)) {
			if (is_null($this -> start_date)) {
				$imgPath = $this -> di -> get('config') -> application -> uploadDir -> event . 'undated/' . $this -> id;
			} else {
				$imgPath = $this -> di -> get('config') -> application -> uploadDir -> event
								. date('Y', strtotime($this -> start_date)) . '/'
								. date('m', strtotime($this -> start_date)) . '/'
								. date('d', strtotime($this -> start_date)) . '/'
								. $this -> id;
			}
		}
		
		if (is_dir($imgPath)) {
			foreach(scandir($imgPath) as $file) {
				if ('.' === $file || '..' === $file) continue;
				is_dir($imgPath . '/' . $file) ? rmdir($imgPath . '/' . $file) : unlink($imgPath . '/' . $file);
		    }
		    
		    rmdir($imgPath);
		}
	}
}