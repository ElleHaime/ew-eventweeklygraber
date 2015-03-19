<?php 

namespace Models;

class Member extends \Library\Model
{
	public $id;
	public $email;
    public $extra_email;
	public $pass;
	public $phone;
	public $name;
	public $address;
	public $location_id;
	public $role;
	public $logo;
	public $auth_type = 'email';
	
	public function initialize()
	{
		parent::initialize();		
		
		$this -> hasOne('location_id', '\Models\Location', 'id', array('alias' => 'location'));
		$this -> hasMany('id', '\Models\Event', 'member_id', array('alias' => 'event'));
		$this -> hasMany('id', '\Models\EventLike', 'member_id', array('alias' => 'event_like'));
		$this -> hasOne('id', '\Models\MemberNetwork', 'member_id', array('alias' => 'network'));
		$this -> hasOne('id', '\Models\EventMember', 'member_id', array('alias' => 'eventpart'));
		$this -> hasOne('id', '\Models\EventMemberFriend', 'member_id', array('alias' => 'eventfriendpart'));
	}
} 