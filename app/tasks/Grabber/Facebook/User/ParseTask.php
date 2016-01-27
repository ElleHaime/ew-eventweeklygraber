<?php

namespace Tasks\Facebook\User;

use \Vendor\Facebook\Extractor,
	\Queue\Consumer\Consumer;


class ParseTask extends \Phalcon\CLI\Task
{
	use \Tasks\Facebook\Grabable;
	
	protected $queueUser;
	protected $queueJob;

	
	public function listenAction()
	{
		$this -> initListener('harvester', 'queueUser');
		$this -> initListener('harvesterJob', 'queueJob');
		
		while (true) {
			$job = $this -> queueUser -> getItem();

            if($job) {
                $this -> queueUser -> ackItem($job);
                $t = new \Jobs\Grabber\Parser\Facebook($this -> getDi());
                $t -> run($job);
            } else {
            	$job = $this -> queueJob -> getItem();
            	
            	if($job) {
            		$this -> queueJob -> ackItem($job);
            		$t = new \Jobs\Grabber\Parser\Facebook($this -> getDi());
            		$t -> run($job);
            	} else {	
            		sleep(2);
            	}
            }
       	} 
	}
}