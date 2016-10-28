<?php

require_once('../../../init.php');

try {
	$console -> handle(['task' => 'Tasks\Facebook\Venue\Parse',
			    		'action' => 'listen']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}

