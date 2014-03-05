<?php

namespace Library\Thread;

class Stack extends \Stackable
{
	public $local;
	public $di;

	public function __construct(\Phalcon\DI $dependencyInjector, $data) {
		$this -> local = $data;
		$this -> di = $dependencyInjector;
	}

	public function run() {
		$this -> worker -> run($this -> di, $this -> local);
	}

	public function getData() { 
		return $this -> local; 
	}
}