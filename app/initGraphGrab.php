<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'observergrab',
                            'action' => 'observegraph']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
