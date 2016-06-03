<?php

namespace Tasks\Facebook\Category;

use \Vendor\FacebookGraph\FacebookSession,
	\Vendor\FacebookGraph\FacebookRequest,
	\Vendor\FacebookGraph\FacebookRequestException, 
	\Models\Classifier;


class GrabTask extends \Phalcon\CLI\Task
{
	use \Tasks\Facebook\Grabable;
	
	const IDLE 		= 'idle';
	const RUNNING 	= 'running';
	
	protected $fbGraphEnabled		= false;
	protected $fbAppAccessToken 	= false;
	protected $query 	= '/search?fields=id,name&topic_filter=all&type=placetopic&limit=1000';
	
	
	public function harvestAction(array $args)
	{
		if (!$this -> fbGraphEnabled) $this -> initGraph();

		$request = $this -> query . '&access_token=' . $args[0];

		try {
			$request = new FacebookRequest($this -> fbSession, 'GET', $request);
			$data = $request -> execute() -> getGraphObject() -> asArray();
		
			if (!empty($data['data'])) {
				foreach ($data['data'] as $key => $value) {
					print_r("....." . $value -> name . "\n\r");
					$object = new Classifier();
					$object -> assign(['fb_uid' => $value -> id, 
							 		   'name' => $value -> name]);
					$object -> save();
				}					
			}
			print_r("\n\rdone\n\r");
			die();
				
		} catch (FacebookRequestException $ex) {
			print_r($ex -> getMessage());
			print_r("\n\r");
			die();
		}
	}
}