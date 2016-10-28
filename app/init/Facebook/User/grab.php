<?php

require_once('../../../init.php');

try {
	$console -> handle(['task' => 'Tasks\Facebook\User\Observer',
						'action' => 'observe']);
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
