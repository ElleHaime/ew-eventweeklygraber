<?php

namespace Tasks;

class RemoverTask extends \Phalcon\CLI\Task
{
	public function removeAction()
	{
		$locations = \Models\Location::find();
		
		foreach ($locations as $loc) {
///print_r($loc -> city . "\n\r");
			$firstShardExists = false;
			$connection = $this -> getDI() -> get('dbMaster');
			$result = $connection -> query('SELECT * FROM shard_mapper_event WHERE criteria = ?', [$loc -> id]);

			while($shard = $result -> fetch()) {
				if (!$firstShardExists || ($firstShardExists && $firstShardExists != $shard['tblname'])) {
					$eventsExists = $connection -> query('SELECT count(id) FROM ' . $shard['tblname'] . ' WHERE location_id = ?', [$loc -> id]);
					$eventsInShard = $eventsExists -> fetch();
///print_r($eventsInShard);
					if ($eventsInShard[0] == 0) {
						$connection -> query('DELETE FROM shard_mapper_event where id = ?', [$shard['id']]); 
					} else {
						$firstShardExists = $shard['tblname'];
					}
				} else {
					$connection -> query('DELETE FROM shard_mapper_event where id = ?', [$shard['id']]);
				}
			}
		}
///print_r("ready\n\r");
///die();
	}
}