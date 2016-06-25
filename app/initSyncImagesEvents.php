<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'Tasks\Synchronization\Sync',
			    		'action' => 'moveImages']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
