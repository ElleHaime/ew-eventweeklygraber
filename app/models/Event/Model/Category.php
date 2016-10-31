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
class Category extends \Engine\Mvc\Model
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
    protected $_orderExpr = 'parent_id';

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var string
     */
    public $name;
     
    /**
     *
     * @var integer
     */
    public $parent_id;
     
    /**
     *
     * @var string
     */
    public $key;
     
    /**
     *
     * @var string
     */
    public $is_default;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("parent_id", "\Models\Event\Model\Category", "id", ['alias' => 'Category']);
        $this->belongsTo("id", "\Models\Event\Model\Tag", "category_id", ['alias' => 'Category']);
        $this->belongsTo("id", "\Models\Event\Model\EventCategory", "category_id", ['alias' => 'Event']);
	$this->belongsTo("id", "\Models\Event\Models\VenueCategory", "category_id", ['alias' => 'Venue']);
    }
     
}
