<?php

require_once('init.php');

try {
        $console -> handle(['task' => 'ebrite',
                            'action' => 'harvest']);
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
