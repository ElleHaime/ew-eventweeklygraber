<?php 

namespace Models;

use Models\Member;

class MemberNetwork extends \Library\Model
{
	public $id;
	public $member_id;
	public $network_id;
	public $account_uid;
	public $account_id;
	public $needCache = true;
	
	//public $cacheData;
	
	public function initialize()
	{
		parent::initialize();		
		
		$this -> belongsTo('member_id', '\Models\Member', 'id', array('alias' => 'member'));
		$this -> belongsTo('network_id', '\Models\Network', 'id', array('alias' => 'network'));
		
		//$this -> cacheData = $this -> getDI() -> get('cacheData');
	}
	
	public function setCache()
	{
	}
}