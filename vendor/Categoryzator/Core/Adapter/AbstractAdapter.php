<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 3/20/14
 * Time: 10:26 AM
 */

namespace Categoryzator\Core\Adapter;


abstract class AbstractAdapter {

    protected $categories = array();

    /**
     * Return array of categories
     *
     * @return array|mixed
     */
    abstract public function getCategories();

} 