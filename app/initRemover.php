<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'Tasks\Remover',
			    		'action' => 'removeLocations']); 
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
