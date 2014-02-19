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
	
	public function initialize()
	{
		$this -> belongsTo('member_id', '\Models\Member', 'id', array('alias' => 'member'));
		$this -> belongsTo('network_id', '\Models\Network', 'id', array('alias' => 'network'));
	}
}