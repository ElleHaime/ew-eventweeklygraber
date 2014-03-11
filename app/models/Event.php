<?php

namespace Models;

class Event extends \Phalcon\Mvc\Model
{
	public $id;
	public $fb_uid;
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

	}
}