#!/usr/bin/php5

<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'cacher',
                            'action' => 'cache']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}