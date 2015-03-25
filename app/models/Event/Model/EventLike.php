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
class EventLike extends \Engine\Mvc\Model
{
    /**
     * Default name column
     * @var string
     */
    protected $_nameExpr = 'event_id';

    /**
     * Default order column
     * @var string
     */
    protected $_orderExpr = 'member_id';

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
     * @var integer
     */
    public $member_id;
     
    /**
     *
     * @var integer
     */
    public $status;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("member_id", "\Models\Event\Model\Member", "id", ['alias' => 'Member']);
        $this->belongsTo("event_id", "\Models\Event\Model\Event", "id", ['alias' => 'Event']);
    }
}
