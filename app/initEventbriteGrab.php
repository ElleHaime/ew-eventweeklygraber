<?php 

require_once('init.php');

try {
    $console -> handle(['task' => 'Tasks\Eventbrite\Grab',
                        'action' => 'harvest']);	
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
