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
class Venue extends \Engine\Mvc\Model
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
     * @var string
     */
    public $fb_uid;
     
    /**
     *
     * @var integer
     */
    public $location_id;
     
    /**
     *
     * @var string
     */
    public $name;
     
    /**
     *
     * @var string
     */
    public $address;
     
    /**
     *
     * @var string
     */
    public $coordinates;
     
    /**
     *
     * @var string
     */
    public $logo;
     
    /**
     *
     * @var double
     */
    public $latitude;
     
    /**
     *
     * @var double
     */
    public $longitude;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("location_id", "\Event\Model\Location", "id", ['alias' => 'Location']);
    }
}
