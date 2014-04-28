#!/usr/bin/php5

<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'listener',
			    		'action' => 'listen']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
