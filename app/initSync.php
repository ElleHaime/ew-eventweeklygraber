<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'sync',
			    		'action' => 'expired']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
