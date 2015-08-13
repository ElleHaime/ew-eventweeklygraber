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
class Event extends \Engine\Mvc\Model
{
    use Sharding {
        Sharding::onConstruct as onParentConstruct;
    }

    /**
     * Default name column
     * @var string
     */
    protected $_nameExpr = 'name';

    /**
     * Default order column
     * @var string
     */
    protected $_orderExpr = 'start_date';

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
     * @var string
     */
    public $fb_creator_uid;
     
    /**
     *
     * @var integer
     */
    public $member_id;
     
    /**
     *
     * @var integer
     */
    public $campaign_id;
     
    /**
     *
     * @var integer
     */
    public $location_id;
     
    /**
     *
     * @var integer
     */
    public $venue_id;
     
    /**
     *
     * @var string
     */
    public $name;
     
    /**
     *
     * @var string
     */
    public $description;
     
    /**
     *
     * @var string
     */
    public $tickets_url;
     
    /**
     *
     * @var string
     */
    public $start_date;
     
    /**
     *
     * @var string
     */
    public $end_date;
     
    /**
     *
     * @var integer
     */
    public $recurring;
     
    /**
     *
     * @var string
     */
    public $event_status;
     
    /**
     *
     * @var integer
     */
    public $event_fb_status;
     
    /**
     *
     * @var string
     */
    public $address;
     
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
    public $logo;
     
    /**
     *
     * @var string
     */
    public $is_description_full;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("member_id", "Models\Event\Model\Member", "id", ['alias' => 'Member']);
        $this->belongsTo("location_id", "Models\Event\Model\Location", "id", ['alias' => 'Location']);
        $this->belongsTo("campaign_id", "Models\Event\Model\Campaign", "id", ['alias' => 'Campaign']);
        $this->belongsTo("venue_id", "Models\Event\Model\Venue", "id", ['alias' => 'Venue']);
        $this->belongsTo("member_id", "Models\Event\Model\Member", "id", ['alias' => 'Member']);
        $this->belongsTo("id", "Models\Event\Model\EventCategory", "event_id", ['alias' => 'Category']);
        $this->belongsTo("id", "Models\Event\Model\EventTag", "event_id", ['alias' => 'Tag']);
    }

    public function onConstruct()
    {
        $this->onParentConstruct();

        //set sharding database connections to dependency injection
        $di = $this->getDI();
        $connections = (array) $this->app->config->connections;
        foreach($connections as $key => $options) {
            $di->set($key, function () use ($options) {
                $db = new \Phalcon\Db\Adapter\Pdo\Mysql([
                    "host" => $options->host,
                    "username" => $options->user,
                    "password" => $options->password,
                    "dbname" => $options->database,
                	"port" => $options->port
                ]);

                return $db;
            });
        }
    }

    public function getSearchSource()
    {
        return 'event';
    }
}
