<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Custom\Observer',
                            'action' => 'observe']);
        
		/*$console -> handle(['task' => 'Tasks\Facebook\Custom\Parse',
				    		'action' => 'listen']); */ 
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}