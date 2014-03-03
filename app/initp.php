#!/usr/bin/php

<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'listener', 
						'action' => 'listen']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
	exit(255);
}