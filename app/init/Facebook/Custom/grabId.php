<?php

require_once('../../../init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Custom\Observer',
                            'action' => 'observeid']);
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}