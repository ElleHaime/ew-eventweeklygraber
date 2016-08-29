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
class VenueImage extends \Engine\Mvc\Model
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
    protected $_orderExpr = 'venue_id';

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
     * @var string
     */
    public $image;
    
    
    /**
     *
     * @var string
     */
    public $type;
    
    
    

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("venue_id", "\Models\Event\Model\Venue", "id", ['alias' => 'Image']);
    }
     
}
