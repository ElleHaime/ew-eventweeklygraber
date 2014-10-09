<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\User\Observer',
                            'action' => 'observe']);
        
		$console -> handle(['task' => 'Tasks\Facebook\User\Parse',
				    		'action' => 'listen']); 
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
