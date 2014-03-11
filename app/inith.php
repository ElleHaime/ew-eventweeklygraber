#!/usr/bin/php

<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'observer', 
						'action' => 'observe']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
	exit(255);
}
