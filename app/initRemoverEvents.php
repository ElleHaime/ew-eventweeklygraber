<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'Tasks\Remover',
						'action' => 'removeEventsIncorrectLocations']);
	 
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
