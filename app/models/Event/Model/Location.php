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
class Location extends \Engine\Mvc\Model
{
    /**
     * Default name column
     * @var string
     */
    protected $_nameExpr = 'city';

    /**
     * Default order column
     * @var string
     */
    protected $_orderExpr = 'city';

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var string
     */
    public $facebook_id;
     
    /**
     *
     * @var string
     */
    public $city;
     
    /**
     *
     * @var string
     */
    public $state;
     
    /**
     *
     * @var string
     */
    public $country;
     
    /**
     *
     * @var string
     */
    public $alias;
     
    /**
     *
     * @var integer
     */
    public $parent_id;
     
    /**
     *
     * @var string
     */
    public $cordinates;
     
    /**
     *
     * @var double
     */
    public $latitudeMin;
     
    /**
     *
     * @var double
     */
    public $longitudeMin;
     
    /**
     *
     * @var double
     */
    public $latitudeMax;
     
    /**
     *
     * @var double
     */
    public $longitudeMax;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("parent_id", "Models\Event\Model\Location", "id", ['alias' => 'Location']);
        $this->belongsTo("id", "Models\Event\Model\Event", "location_id", ['alias' => 'Event']);
    }
}
