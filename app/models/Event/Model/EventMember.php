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
class EventMember extends \Engine\Mvc\Model
{
    use Sharding {
        Sharding::onConstruct as onParentConstruct;
    }

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
     * @var string
     */
    public $member_status;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("member_id", "\Event\Model\Member", "id", ['alias' => 'Member']);
        $this->belongsTo("event_id", "\Event\Model\Event", "id", ['alias' => 'Event']);
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
                    "dbname" => $options->database
                ]);

                return $db;
            });
        }
    }

}
