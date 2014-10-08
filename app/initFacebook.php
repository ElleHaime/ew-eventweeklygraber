<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Observer',
                            'action' => 'observe']);
        
		$console -> handle(['task' => 'Tasks\Facebook\Parse',
				    		'action' => 'listen']); 
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
