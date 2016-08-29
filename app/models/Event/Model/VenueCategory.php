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
class VenueCategory extends \Engine\Mvc\Model
{
    /**
     * Default name column
     * @var string
     */
    protected $_nameExpr = 'venue_id';

    /**
     * Default order column
     * @var string
     */
    protected $_orderExpr = 'category_id';

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var integer
     */
    public $venue_id;
     
    /**
     *
     * @var integer
     */
    public $category_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("venue_id", "\Models\Event\Model\Venue", "id", ['alias' => 'Venue']);
        $this->belongsTo("category_id", "\Models\Event\Model\Category", "id", ['alias' => 'Category']);
    }
     
}
