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
class Tag extends \Engine\Mvc\Model
{
    /**
     * Default name column
     * @var string
     */
    protected $_nameExpr = 'name';

    /**
     * Default order column
     * @var string
     */
    protected $_orderExpr = 'name';

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var integer
     */
    public $category_id;
     
    /**
     *
     * @var string
     */
    public $name;
     
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
        $this->belongsTo("category_id", "\Event\Model\Category", "id", ['alias' => 'Category']);
        $this->belongsTo("tag_id", "\Event\Model\EventTag", "id", ['alias' => 'Event']);
    }
}
