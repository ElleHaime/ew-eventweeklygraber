<?php 

require_once('init.php');

try {
    $console -> handle(['task' => 'Tasks\Eventbrite\Grab',
                        'action' => 'harvest']);	
    
	$console -> handle(['task' => 'Tasks\Eventbrite\Parse',
			    		'action' => 'listen']);
	
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
