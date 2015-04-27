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
    
    public $cacheData; 


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
		$this -> hasMany('id', '\Models\EventLike', 'event_id', array('alias' => 'event_like'));
        $this -> hasMany('id', '\Models\EventTag', 'event_id', array('alias' => 'event_tag'));

        $this -> cacheData = $this -> getDI() -> get('cacheData');
	}
	
	
	public function getCreators()
	{
		$result = [];
		
		$shards = $this -> getAvailableShards();
		foreach ($shards as $cri) {
			$this -> setShard($cri);
			$creators = self::find();

			if ($creators -> count() != 0) {
				foreach ($creators as $val) {
					if (!is_null($val -> fb_creator_uid) && $val -> fb_creator_uid != '') {
						$result[$val -> fb_creator_uid] = $val -> fb_creator_uid;
					}
				}
			}
		} 

		return $result;
	}
	
	
	public function existsInShardsBySourceId($id, $source = 'fb')
	{
		$shards = $this -> getAvailableShards();
		foreach ($shards as $cri) {
			$e = new $this;
			$e -> setShard($cri);
			$event = $e::findFirst($source . '_uid="' . $id . '"');
			
			if ($event) {
				return $event;
			} 
		}
		
		return false;
	}
	

	public function setCache()
	{
		/*$query = new \Phalcon\Mvc\Model\Query("SELECT id, fb_uid
												FROM Models\Event
												WHERE event_status = 1", $this -> getDI());
		$events = $query -> execute();
	
		if ($events) {
			foreach ($events as $event) {
				if ($event -> fb_uid && !$this -> cacheData -> exists('fbe_' . $event -> fb_uid)) {
					$this -> cacheData -> save('fbe_' . $event -> fb_uid, $event -> id);
				}
			}
		}
		$this -> cacheData -> save('fb_events', 'cached'); */
	}
	
}