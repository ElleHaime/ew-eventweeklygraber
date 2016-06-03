<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Category\Observer',
                            'action' => 'observe']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
