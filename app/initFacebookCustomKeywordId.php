<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Custom\Observer',
                            'action' => 'observeid',
        					'params' => 3]);
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}