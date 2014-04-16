<?php 

namespace Models;

use Models\Member;

class MemberNetwork extends \Phalcon\Mvc\Model
{
	public $id;
	public $member_id;
	public $network_id;
	public $account_uid;
	public $account_id;
	public $needCache = true;
	
	public $cacheData;
	
	public function initialize()
	{
		$this -> belongsTo('member_id', '\Models\Member', 'id', array('alias' => 'member'));
		$this -> belongsTo('network_id', '\Models\Network', 'id', array('alias' => 'network'));
		
		$this -> cacheData = $this -> getDI() -> get('cacheData');
	}
	
	public function setCache()
	{
		$query = new \Phalcon\Mvc\Model\Query("SELECT member_id, account_uid FROM Models\MemberNetwork", $this -> getDI());
		$members = $query -> execute() -> toArray();
	
		if ($members) {
			foreach($members as $key => $member) {
				$this -> cacheData -> save('member_' . $member['account_uid'], $member['member_id']);
			}
			$this -> cacheData -> save('fb_members', 'cached');
		}
	}
}