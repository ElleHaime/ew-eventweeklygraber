<?php
/**
 * Class CategoryzatorException
 *
 * @author   Slava Basko <basko.slava@gmail.com>
 */

namespace Categoryzator\Core;


class CategoryzatorException extends \Exception
{

    final public function getMsg() {
        ob_start();
        echo 'ERROR: '.$this->getMessage();
        echo '<br>';
        print_r($this->getTraceAsString());
        $out = ob_get_clean();
        return $out;
    }

} 