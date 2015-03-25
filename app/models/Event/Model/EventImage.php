<?php
/**
 * @namespace
 */
namespace Models\Event\Model;

use Sharding\Core\Env\Phalcon as Sharding;

/**
 * Class event.
 *
 * @category   Module
 * @package    Event
 * @subpackage Model
 */
class EventImage extends \Engine\Mvc\Model
{
    /**
     * Default name column
     * @var string
     */
    protected $_nameExpr = 'image';

    /**
     * Default order column
     * @var string
     */
    protected $_orderExpr = 'event_id';

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var integer
     */
    public $event_id;
     
    /**
     *
     * @var string
     */
    public $image;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("event_id", "\Models\Event\Model\Event", "id", ['alias' => 'Event']);
    }
     
}
