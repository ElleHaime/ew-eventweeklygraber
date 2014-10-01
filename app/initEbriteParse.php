<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'listener',
			    		'action' => 'listenebrite']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
