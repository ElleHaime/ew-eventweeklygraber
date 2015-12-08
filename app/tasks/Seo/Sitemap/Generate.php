<?php

namespace Tasks\Seo\Sitemap;

use \Models\Cron;

class GenerateTask extends \Phalcon\CLI\Task
{
	public function generateAction() 
	{
		$job = new \Jobs\Seo\Sitemap\Generator($this -> getDi());
		$job -> run();
	}
}