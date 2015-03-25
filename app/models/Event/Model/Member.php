<?php
/**
 * @namespace
 */
namespace Models\Event\Model;

use Phalcon\Mvc\Model\Validator\Email as Email;

/**
 * Class event.
 *
 * @category   Module
 * @package    Event
 * @subpackage Model
 */
class Member extends \Engine\Mvc\Model
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
    protected $_orderExpr = 'location_id';

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var string
     */
    public $email;
     
    /**
     *
     * @var string
     */
    public $extra_email;
     
    /**
     *
     * @var string
     */
    public $pass;
     
    /**
     *
     * @var string
     */
    public $phone;
     
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
     * @var integer
     */
    public $location_id;
     
    /**
     *
     * @var string
     */
    public $auth_type;
     
    /**
     *
     * @var string
     */
    public $role;
     
    /**
     *
     * @var string
     */
    public $logo;
     
    /**
     * Validations and business logic
     */
    public function validation()
    {

        $this->validate(
            new Email(
                array(
                    "field"    => "email",
                    "required" => true,
                )
            )
        );
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo("location_id", "\Models\Event\Model\Location", "id", ['alias' => 'Location']);
    }
}
