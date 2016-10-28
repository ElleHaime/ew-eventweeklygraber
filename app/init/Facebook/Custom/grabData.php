<?php

require_once('../../../init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Custom\Observer',
                            'action' => 'observedata']);
        
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}