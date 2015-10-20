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
	}
	
	public function getCreators()
	{
		$result = [];
		
		$shards = $this -> getAvailableShards();
		foreach ($shards as $cri) {
			$this -> setShard($cri);
			$creators = self::find(['fb_creator_uid is not null',
									 'distinct' => 'fb_creator_uid']);

			if ($creators -> count() != 0) {
				foreach ($creators as $val) {
					$result[$val -> fb_creator_uid] = $val -> fb_creator_uid;
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
	
	
	public function archive()
	{
		// move: event_image, event_site, event_tag, event_category
		unset($this -> memberlike);
		unset($this -> memberpart);
		unset($this -> memberfriendpart);
		
 		$archive = new \Models\EventArchive();
 		$archive -> assign(['event' => serialize($this),
 							'archived' => date('Y-m-d H:i:s')]);
		if ($archive -> save()) {
 			(new \Models\Featured) -> deleteEventFeatured($this -> id);
 			(new \Models\EventRating) -> deleteEventRating($this -> id);
			(new \Models\EventLike) -> deleteEventLiked($this -> id);
 			(new \Models\EventMember) -> deleteEventJoined($this -> id);
 			(new \Models\EventMemberFriend) -> deleteEventFriend($this -> id);
			
			(new \Models\EventTag) -> deleteEventTag($this);
			(new \Models\EventCategory) -> deleteEventCategory($this);
			
			$this -> delete();
		}		
		
		return;		
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
		print_r(date('Y-m-d H:i:s', strtotime($date)) . "\n\r"); 
		if (strtotime($date) > time()) {
			return true;
		} else {
			return false;
		}
	}

	public function setCache()
	{
	}
}