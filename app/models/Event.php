<?php

namespace Models;

class Event extends \Phalcon\Mvc\Model
{
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
		$query = new \Phalcon\Mvc\Model\Query("SELECT DISTINCT Models\Event.fb_creator_uid
													FROM Models\Event
													WHERE Models\Event.fb_creator_uid IS NOT NULL", $this -> getDI());
														
		$creators = $query -> execute();
		
		if ($creators -> count() != 0) {
			foreach ($creators as $val) {
				$result[] = $val -> fb_creator_uid;
			}
		}
		
		return $result;
	}
	
	
	public function getCreatedEventsCount($uId)
	{
		if ($uId) {
			$query = new \Phalcon\Mvc\Model\Query("SELECT Models\Event.id, Models\Event.fb_uid
													FROM Models\Event
													WHERE Models\Event.deleted = 0
													AND Models\Event.member_id = " . $uId, $this -> getDI());
			$event = $query -> execute();
			return $event;
		} else {
			return 0;
		}
	}
	

	public function setCache()
	{
		$query = new \Phalcon\Mvc\Model\Query("SELECT id, fb_uid
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
		$this -> cacheData -> save('fb_events', 'cached');
	}
	
}