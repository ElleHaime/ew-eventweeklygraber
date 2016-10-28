<?php 

require_once('../../init.php');

try {
    $console -> handle(['task' => 'Tasks\Eventbrite\Grab',
                        'action' => 'harvestIncorrect']);	
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
