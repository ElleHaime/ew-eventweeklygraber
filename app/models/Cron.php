<?php

namespace Models;

class Cron extends \Library\Model
{
	const STATE_PENDING 	= 0;
	const STATE_HANDLING 	= 1;
	const STATE_EXECUTED 	= 2;
	const STATE_INTERRUPTED = 3;

	const FB_TASK_NAME 						= 'extract_facebook_events';
	const FB_GET_ID_TASK_NAME 				= 'extract_custom_facebook_events_id';
	const FB_BY_ID_TASK_NAME				= 'extract_custom_facebook_events_data';
	const FB_CREATOR_TASK_NAME 				= 'extract_creators_facebook_events';
	const FB_VENUE_TASK_NAME 				= 'extract_venue_facebook_events';
	
	const RECAT_TASK_NAME 					= 'recategorize_events';
	
	const FB_USER_CREATE_TASK_TYPE			= 'user_event';
	const FB_USER_PAGE_TASK_TYPE			= 'user_page_event';
	const FB_USER_FRIEND_TASK_TYPE			= 'friend_event';
	const FB_USER_FRIEND_GOING_TASK_TYPE	= 'friend_going_event';
	const FB_USER_GOING_TASK_TYPE			= 'user_going_event';
	const FB_USER_LIKE_TASK_TYPE			= 'page_event';
	const FB_CREATOR_TASK_TYPE				= 'creators';
	const FB_CREATOR_VENUE_TASK_TYPE		= 'creators_venue';
	const FB_CUSTOM_TASK_TYPE				= 'custom';
	
	
	public $id;
	public $name;
	public $description;
	public $path;
	public $member_id;
	public $parameters;
	public $state;
	public $hash;
}
