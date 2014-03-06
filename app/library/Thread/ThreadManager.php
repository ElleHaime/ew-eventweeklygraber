<?php

namespace Library\Thread;

class ThreadManager
{
	public $maxThreads;
	public $workers;
	public $status;
	public $di;

	public function __construct(\Phalcon\DI $di, $maxThreads = 10) {
		$this -> maxThreads = $maxThreads;
		$this -> di = $di;
	}

	/* submit Stackable to Worker */
	public function submit(\Stackable $stackable) {
		if (count($this -> workers) < $this -> maxThreads) {
			$id = count($this -> workers);
			$this -> workers[$id] = new \Jobs\Parser\ThFacebook($this -> di, $id);
			$this -> workers[$id] -> start(PTHREADS_INHERIT_NONE);

			if ($this -> workers[$id] -> stack($stackable)) {
				return $stackable;
			} else {
				trigger_error(sprintf("failed to push Stackable onto %s", $this->workers[$id]->getName()), E_USER_WARNING);
			}
		}
		if (($select = $this -> workers[array_rand($this -> workers)])) {

			if ($select -> stack($stackable)) {
				return $stackable;
			} else {
				trigger_error(sprintf("failed to stack onto selected worker %s", $select -> getName()), E_USER_WARNING);
			}
		} else {
			trigger_error(sprintf("failled to select a worker for Stackable"), E_USER_WARNING);
		}

		return false;
	}


	public function shutdown() {
		foreach($this -> workers as $worker) {
			$this -> status[$worker -> getThreadId()] = $worker -> shutdown();
		}
	}



}