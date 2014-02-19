<?php
/**
 * Class Parser
 *
 * @author   Slava Basko <basko.slava@gmail.com>
 */

namespace Categoryzator\Core;


class Parser {

    private $categories = array();

    /**
     * Read categories in files
     */
    public function __construct()
    {
        $path = dirname(dirname(__FILE__));
        $file = $path.'/categories.php';
        if (!is_readable($path)) {
            throw new \Exception('File with categories do not exist!');
        }
        $this->categories = include $file;
        return $this;
    }

    /**
     * Return array of categories
     *
     * @return array|mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

} 