<?php
/**
 * Class Text
 *
 * @author   Slava Basko <basko.slava@gmail.com>
 */

namespace Categoryzator\Core;


class Text {

    public $content = array();

    public $countedWords = null;

    public $countEntry = null;

    public $category = null;

    public $tag = null;

    public $returnTag = false;

    public function addContent($content)
    {
        $this->content[] = $content;
        return $this;
    }

    public function returnTag($tag = false)
    {
        $this->returnTag = $tag;
        return $this;
    }

} 