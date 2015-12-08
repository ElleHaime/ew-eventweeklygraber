<?php
/**
 * @namespace
 */
namespace Models\Event\Grid\Search;

use
    Engine\Crud\Grid,
    Engine\Crud\Grid\Column,
    Engine\Crud\Grid\Filter\Search\Elasticsearch as Filter,
    Engine\Crud\Grid\Filter\Field,
    Engine\Filter\SearchFilterInterface as Criteria;

/**
 * Class Events.
 *
 * @category   Module
 * @package    Event
 * @subpackage Grid
 */
class EventSearch extends Grid
{
    /**
     * Container adapter class name
     * @var string
     */
    protected $_containerAdapter = 'Mysql\Elasticsearch';

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
     * Default grid params
     * @var array
     */
    protected $_defaultParams = [
        'sort' => false,
        'direction' => false,
        'page' => 1,
        'limit' => 10
    ];

    /**
     * Initialize grid columns
     *
     * @return void
     */
    protected function _initColumns()
    {
        $this->setStrictMode(false);

        $this->_columns = [
            'id' => new Column\Primary('Id'),
            'name' => new Column\Text('Name', 'name'),
            'member' => new Column\JoinOne("Member", "\Models\Event\Model\Member"),
            'member_id' => new Column\Text("Member", "member_id"),
            'campaign' => new Column\Text("Campaign", "campaign_id"),
            'location' => new Column\Text("Location", "location_id"),
            'fb_uid' => new Column\Text('Facebook uid', 'fb_uid'),
            'fb_creator_uid' => new Column\Text('Facebook creator uid', 'fb_creator_uid'),
            'description' => new Column\Text('Description', 'description', false),
            'tickets_url' => new Column\Text('tickets_url', 'tickets_url'),
            'start_date' => new Column\Date('Start date', 'start_date'),
            'end_date' => new Column\Date('End date', 'end_date'),
            'recurring' => new Column\Text('recurring', 'recurring'),
            'event_status' => new Column\Text('event_status', 'event_status'),
            'event_fb_status' => new Column\Text('event_fb_status', 'event_fb_status'),
            'address' => new Column\Text('Address', 'address'),
            'latitude' => new Column\Text('latitude', 'latitude'),
            'longitude' => new Column\Text('longitude', 'longitude'),
            'logo' => new Column\Text('Logo', 'logo')
        
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
                'location'      => Criteria::CRITERIA_LIKE,
                'tag'           => Criteria::CRITERIA_LIKE,
                'category'      => Criteria::CRITERIA_LIKE,
                'name'          => Criteria::CRITERIA_BEGINS,
                'id'          	=> Criteria::CRITERIA_EQ,
                'description'   => Criteria::CRITERIA_BEGINS,
            	'member'		=> Criteria::CRITERIA_EQ,
            ], null, 280, null, 255, false),
            'searchLocationField' => new Field\Join("Location", "\Models\Event\Model\Location"),
            'searchCategory' => new Field\Join("Category", "\Models\Event\Model\Category", false, null, ["\Models\Event\Model\EventCategory", "\Models\Event\Model\Category"]),
            'searchTitle' => new Field\Name("Name", null, Criteria::CRITERIA_LIKE),
            'searchId' => new Field\Primary("Id", null, Criteria::CRITERIA_IN),
        	'searchNotId' => new Field\Standart("Id", 'id', null, Criteria::CRITERIA_NOTIN),
            'searchMember' => new Field\Standart('MemberId', 'member_id', null, Criteria::CRITERIA_EQ),
            'searchDesc' => new Field\Standart("Desc", "description"),
            'searchTag' => new Field\Join("Tags", "\Models\Event\Model\Tag", false, null, ["\Models\Event\Model\EventTag", "\Models\Event\Model\Tag"]),
        		
			'searchStartDate' => new Field\Date('Event start', 'start_date', null, Criteria::CRITERIA_MORE),
        	'searchEndDate' => new Field\Date('Event end', 'end_date', null, Criteria::CRITERIA_LESS),
        		
        	'searchLatitude' => new Field\Standart('Latitude', 'latitude', null),
        	'searchLongitude' => new Field\Standart('Longitude', 'longitude', null),
        	'searchAddress' => new Field\Standart('Address', 'address', null, Criteria::CRITERIA_LIKE),
        	'searchStatus' => new Field\Standart('Status', 'event_status', null, Criteria::CRITERIA_EQ),
        	'searchLogo' => new Field\Standart('Logo', 'logo'),
        	'searchCompound' => new Field\Compound('bububu', 'bububu', [
                'compoundTag' => new Field\Join("Tags", "\Models\Event\Model\Tag", false, null, ["\Models\Event\Model\EventTag", "\Models\Event\Model\Tag"]),
        		'compoundCategory' => new Field\Join("Category", "\Models\Event\Model\Category", false, null, ["\Models\Event\Model\EventCategory", "\Models\Event\Model\Category"]),
        		'compoundTitle' => new Field\Name("Name", 'name', Criteria::CRITERIA_LIKE)
            ]),
            'searchCompoundUser' => new Field\Compound('bububu2', 'bububu2', [
                'compoundTag2' => new Field\Join("Tags", "\Models\Event\Model\Tag", false, null, ["\Models\Event\Model\EventTag", "\Models\Event\Model\Tag"]),
                'searchCompound3' => new Field\Compound('bububu3', 'bububu3', [
                    'compoundCategory3' => new Field\Join("Category", "\Models\Event\Model\Category", false, null, ["\Models\Event\Model\EventCategory", "\Models\Event\Model\Category"]),
                    'compoundTitle3' => new Field\Name("Name", 'name', Criteria::CRITERIA_LIKE)
                ])
            ], Field\Compound::OPERATOR_AND),
        ], null, 'get');
    }

    /**
     * Setup container
     *
     * @return void
     */
    protected function _setupContainer()
    {
        $this->_container->useIndexData();
    }
}
