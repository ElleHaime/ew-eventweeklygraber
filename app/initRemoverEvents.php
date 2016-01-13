<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'Tasks\Remover',
						'action' => 'removeEventsPastImages']);
	 
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
