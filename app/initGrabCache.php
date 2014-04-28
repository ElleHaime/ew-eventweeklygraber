#!/usr/bin/php

<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'cachergrab',
                            'action' => 'cache']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}