<?php

require_once('../../../init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Creator\Observer',
                            'action' => 'observe']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
