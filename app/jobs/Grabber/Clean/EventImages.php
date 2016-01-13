<?php

namespace Jobs\Grabber\Clean;

use Models\Event;

class EventImages
{
	public $di;

	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> di = $dependencyInjector;
	}
	
	
	public function run()
	{
		$dirs = scandir($this -> di -> get('config') -> application -> uploadDir);

		foreach ($dirs as $f) {
			print_r(".");
			if ('.' === $f || '..' === $f) continue;

			$e = (new Event()) -> setShardById($f);
			$exists = $e::findFirst($f);
			if (!$exists) {
				$this -> deleteRec($this -> di -> get('config') -> application -> uploadDir . $f); 				
			}
		}
		
		print_r("done\n\r"); die();
	}
	
	
	protected function deleteRec($path)
	{
		if (is_dir($path)) {
			foreach(scandir($path) as $file) {
				if ('.' === $file || '..' === $file) continue;
				is_dir($path . '/' . $file) ? $this -> deleteRec($path . '/' . $file) : unlink($path . '/' . $file);
			}
	
			rmdir($path);
		}
	}
}