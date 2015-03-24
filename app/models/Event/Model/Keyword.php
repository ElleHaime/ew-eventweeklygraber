<?php
/**
 * @namespace
 */
namespace Models\Event\Model;

/**
 * Class event.
 *
 * @category   Module
 * @package    Event
 * @subpackage Model
 */
class Keyword extends \Engine\Mvc\Model
{
    /**
     * Name of column like dafault name column
     * @var string
     */
    protected $_nameExpr = 'key';

    /**
     * Default column name
     * @var string
     */
    protected $_orderExpr = 'tag_id';

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var integer
     */
    public $tag_id;
     
    /**
     *
     * @var string
     */
    public $key;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("tag_id", "\Event\Model\Tag", "id", ['alias' => 'Tag']);
    }
}
