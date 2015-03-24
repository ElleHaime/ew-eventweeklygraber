<?php
/**
 * @namespace
 */
namespace Models\Event\Grid\Search;

use
    Engine\Crud\Grid,
    Engine\Crud\Grid\Column,
    Engine\Crud\Grid\Filter as Filter,
    Engine\Crud\Grid\Filter\Field,
    Engine\Filter\SearchFilterInterface as Criteria;

/**
 * Class Events.
 *
 * @category   Module
 * @package    Event
 * @subpackage Grid
 */
class Event extends Grid
{
    /**
     * Container adapter class name
     * @var string
     */
    protected $_containerAdapter = 'Mysql';

    /**
     * Grid title
     * @var string
     */
    protected $_title = 'Event';

    /**
     * Container model
     * @var string
     */
    protected $_containerModel = '\Models\Event\Model\Event';

    /**
     * Container condition
     * @var array|string
     */
    protected $_containerConditions = null;

    /**
     * Initialize grid columns
     *
     * @return void
     */
    protected function _initColumns()
    {
        $this->_columns = [
            'id' => new Column\Primary('Id'),
            'name' => new Column\Text('Name', 'name'),
            'status' => new Column\Collection('Status', 'event_status', [1 => 'Active', 2 => 'Unpublished', 3 => 'Hidden']),
            'member' => new Column\JoinOne("Member", "\Event\Model\Member"),
            'campaign' => new Column\JoinOne("Campaign", "\Event\Model\Campaign"),
            'location' => new Column\JoinOne("Location", "\Event\Model\Location"),
            'venue' => new Column\JoinOne("Venue", "\Event\Model\Venue"),
            //'category' => new Column\JoinOne("Category", "\Event\Model\Category"),
            /*'member' => new Column\Numeric("Member", "member_id"),
            'campaign' => new Column\Numeric("Campaign", "campaign_id"),
            'location' => new Column\Numeric("Location", "location_id"),
            'venue' => new Column\Numeric("Venue", "venue_id"),*/
            'fb_uid' => new Column\Text('Facebook uid', 'fb_uid'),
            'fb_creator_uid' => new Column\Text('Facebook creator uid', 'fb_creator_uid'),
            'description' => new Column\Text('Description', 'description', false),
            'tickets_url' => new Column\Text('tickets_url', 'tickets_url'),
            'start_date' => new Column\Date('Start date', 'start_date'),
            'end_date' => new Column\Date('End date', 'end_date'),
            'recurring' => new Column\Text('recurring', 'recurring'),
            'event_fb_status' => new Column\Text('event_fb_status', 'event_fb_status'),
            'address' => new Column\Text('Address', 'address'),
            'latitude' => new Column\Text('latitude', 'latitude'),
            'longitude' => new Column\Text('longitude', 'longitude'),
            'logo' => new Column\Text('Logo', 'logo'),
            'is_description_full' => new Column\Text('is_description_full', 'is_description_full'),
        ];

    }

    /**
     * Initialize grid filters
     *
     * @return void
     */
    protected function _initFilters()
    {
        $this->_filter = new Filter([
            'search' => new Field\Search('Search', 'search', [
                Criteria::COLUMN_NAME   => Criteria::CRITERIA_LIKE,
                'description'           => Criteria::CRITERIA_LIKE,
                'location'              => Criteria::CRITERIA_LIKE,
                'category'              => Criteria::CRITERIA_LIKE,
                'tag'                   => Criteria::CRITERIA_LIKE,
            	'id'          			=> Criteria::CRITERIA_EQ
            ]),
            'name' => new Field\Name("Name"),
            'desc' => new Field\Standart("Description", 'description'),
            'location' => new Field\Join("Location", "\Event\Model\Location"),
            'campaign' => new Field\Join("Campaign", "\Event\Model\Campaign"),
            'category' => new Field\Join("Category", "\Event\Model\Category", false, null, ["\Event\Model\EventCategory", "\Event\Model\Category"]),
            'member' => new Field\Join("Member", "\Event\Model\Member"),
            'tag' => new Field\Join("Tags", "\Event\Model\Tag", false, null, ["\Event\Model\EventTag", "\Event\Model\Tag"]),
            'start_date' => new Field\Date('Event start', null, null, Criteria::CRITERIA_MORE),
        	'latitude' => new Field\Standart('Latitude', 'latitude', null, Criteria::CRITERIA_EQ),
        	'longitude' => new Field\Standart('Longitude', 'longitude', null, Criteria::CRITERIA_EQ),
        	'address' => new Field\Standart('Address', 'address', null, Criteria::CRITERIA_LIKE),
        	'logo' => new Field\Standart('Logo', 'logo', null, Criteria::CRITERIA_EQ)
        ], null, 'get');

        //$tag = $this->_filter->getFieldByKey('tag');
        //$tag->category = "\Event\Model\Category";
    }
}
