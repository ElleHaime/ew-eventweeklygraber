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
		$cfg = ['host' => $this -> di -> get('config') -> database -> host,
				'username' => $this -> di -> get('config') -> database -> username,
				'password' => $this -> di -> get('config') -> database -> password,
				'dbname' => $this -> di -> get('config') -> database -> dbname];
		$conn = new \Phalcon\Db\Adapter\Pdo\Mysql($cfg);
		
		$dirs = scandir($this -> di -> get('config') -> application -> uploadDir -> event );

		foreach ($dirs as $f) {
			if ('.' === $f || '..' === $f) continue;
			$exists = false;
			
			$shardId = explode('_', $f)[1];
			$result =  $conn -> query("SELECT tblname FROM shard_mapper_event where id = ?", [$shardId]);

			if ($result -> fetch()['criteria']) {
				$e = (new Event()) -> setShardById($f);
				$exists = $e -> strictSqlQuery()
								-> addQueryCondition('id="' . $f . '"')
								-> addQueryFetchStyle('\Models\Event')
								-> selectRecords();
			} 
			if (!$exists) {
				$this -> deleteRec($this -> di -> get('config') -> application -> uploadDir -> event . $f); 				
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