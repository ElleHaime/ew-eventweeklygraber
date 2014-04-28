<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'cacherapp',
                            'action' => 'cache']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}