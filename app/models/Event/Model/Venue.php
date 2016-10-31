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
     *
     * @var string
     */
    public $intro;
    
    /**
     *
     * @var string
     */
    public $description;
    
    /**
     *
     * @var string
     */
    public $worktime;
    
    /**
     *
     * @var string
     */
    public $phone;
    
    /**
     *
     * @var string
     */
    public $email;
    
    /**
     *
     * @var string
     */
    public $transit;
    
    /**
     *
     * @var string
     */
    public $pricerange;
    
    /**
     *
     * @var string
     */
    public $services;
    
    /**
     *
     * @var string
     */
    public $specialties;
    
    /**
     *
     * @var string
     */
    public $payment;
    
    /**
     *
     * @var string
     */
    public $parking;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("location_id", "\Models\Event\Model\Location", "id", ['alias' => 'Location']);
        $this->belongsTo("id", "Models\Event\Model\VenueCategory", "venue_id", ['alias' => 'Category']);
        $this->belongsTo("id", "Models\Event\Model\VenueTag", "venue_id", ['alias' => 'Tag']);
    }
}
