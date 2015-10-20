<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'Tasks\Facebook\Creator\Observer',
                            'action' => 'observeVenue']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
