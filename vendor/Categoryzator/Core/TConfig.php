<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 3/20/14
 * Time: 10:44 AM
 */

namespace Categoryzator\Core;


trait TConfig {

    private $config = null;

    private function initializeConfig()
    {
        $path = dirname(dirname(__FILE__));
        $file = $path.'/config.php';
        if (!is_readable($path)) {
            throw new \Exception('File with categories do not exist!');
        }

        $this->config = include $file;
    }

} 