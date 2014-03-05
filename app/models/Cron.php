<?php

namespace Models;

class Cron extends \Phalcon\Mvc\Model
{
	const STATE_PENDING = 0;
	const STATE_HANDLING = 1;
	const STATE_EXECUTED = 2;

	public $id;
	public $name;
	public $description;
	public $path;
	public $member_id;
	public $parameters;
	public $state;
}
