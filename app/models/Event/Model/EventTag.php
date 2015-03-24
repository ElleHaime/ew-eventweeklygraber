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
class EventTag extends \Engine\Mvc\Model
{
    use Sharding {
        Sharding::onConstruct as onParentConstruct;
    }

    /**
     * Default name column
     * @var string
     */
    protected $_nameExpr = 'tag_id';

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
     * @var integer
     */
    public $tag_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("event_id", "Event\Model\Event", "id", ['alias' => 'Event']);
        $this->belongsTo("tag_id", "Event\Model\Tag", "id", ['alias' => 'Tag']);
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
