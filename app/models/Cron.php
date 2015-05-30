<?php

namespace Models;

class Cron extends \Library\Model
{
	const STATE_PENDING 	= 0;
	const STATE_HANDLING 	= 1;
	const STATE_EXECUTED 	= 2;
	const STATE_INTERRUPTED = 3;

	const FB_TASK_NAME 			= 'extract_facebook_events';
	const FB_GET_ID_TASK_NAME 	= 'extract_custom_facebook_events_id';
	const FB_BY_ID_TASK_NAME	= 'extract_custom_facebook_events_data';
	const FB_CREATOR_TASK_NAME = 'extract_creators_facebook_events';
	
	const RECAT_TASK_NAME 		= 'recategorize_events';
	
	public $id;
	public $name;
	public $description;
	public $path;
	public $member_id;
	public $parameters;
	public $state;
	public $hash;
}
