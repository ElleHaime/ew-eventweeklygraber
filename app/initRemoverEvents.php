<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'Tasks\Remover',
						'action' => 'removeEvents']);
	 
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
