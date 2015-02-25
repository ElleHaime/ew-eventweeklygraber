<?php

require_once('init.php');

try {
		$console -> handle(['task' => 'Tasks\Cache\Fbuids',
							'action' => 'cache']);

} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}