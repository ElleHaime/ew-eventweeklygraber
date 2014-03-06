<?php

namespace Library\Thread;

class DataStack extends \Stackable
{
	public function __construct($data) {
		$this -> local = $data;
	}

	public function run() {
		if ($this -> worker) {
			$this -> worker -> addData($this -> getData());
		}
	}

	public function getData() { 
		return $this -> local; 
	}
}