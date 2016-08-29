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
 * Class Venues.
 *
 * @category   Module
 * @package    Event
 * @subpackage Grid
 */
class VenueSearch extends Grid
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
    protected $_title = 'Venue';

    /**
     * Container model
     * @var string
     */
    protected $_containerModel = '\Models\Event\Model\Venue';

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
            'location' => new Column\Text("Location", "location_id"),
            'fb_uid' => new Column\Text('Facebook uid', 'fb_uid'),
            'fb_username' => new Column\Text('Facebook username', 'fb_username'),
            'description' => new Column\Text('Description', 'description', false),
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
    					'intro'   => Criteria::CRITERIA_LIKE
    			], null, 280, null, 255, false),
    			'searchLocationField' => new Field\Join("Location", "\Models\Event\Model\Location"),
    			'searchCategory' => new Field\Join("Category", "\Models\Event\Model\Category", false, null, ["\Models\Event\Model\VenueCategory", "\Models\Event\Model\Category"]),
    			'searchTitle' => new Field\Name("Name", null, Criteria::CRITERIA_LIKE),
    			'searchId' => new Field\Primary("Id", null, Criteria::CRITERIA_IN),
    			'searchNotId' => new Field\Standart("Id", 'id', null, Criteria::CRITERIA_NOTIN),
    			'searchDesc' => new Field\Standart("Desc", "description"),
    			'searchTag' => new Field\Join("Tags", "\Models\Event\Model\Tag", false, null, ["\Models\Event\Model\VenueTag", "\Models\Event\Model\Tag"]),
    			'searchLatitude' => new Field\Standart('Latitude', 'latitude', null),
    			'searchLongitude' => new Field\Standart('Longitude', 'longitude', null),
    			'searchAddress' => new Field\Standart('Address', 'address', null, Criteria::CRITERIA_LIKE),
    			'searchLogo' => new Field\Standart('Logo', 'logo'),
    			'searchCompound' => new Field\Compound('bububu', 'bububu', [
    					'compoundTag' => new Field\Join("Tags", "\Models\Event\Model\Tag", false, null, ["\Models\Event\Model\VenueTag", "\Models\Event\Model\Tag"]),
    					'compoundCategory' => new Field\Join("Category", "\Models\Event\Model\Category", false, null, ["\Models\Event\Model\VenueCategory", "\Models\Event\Model\Category"]),
    					'compoundTitle' => new Field\Name("Name", 'name', Criteria::CRITERIA_LIKE)
    			]),
    			'searchCompoundUser' => new Field\Compound('bububu2', 'bububu2', [
    					'compoundTag2' => new Field\Join("Tags", "\Models\Event\Model\Tag", false, null, ["\Models\Event\Model\VenueTag", "\Models\Event\Model\Tag"]),
    					'searchCompound3' => new Field\Compound('bububu3', 'bububu3', [
    							'compoundCategory3' => new Field\Join("Category", "\Models\Event\Model\Category", false, null, ["\Models\Event\Model\VenueCategory", "\Models\Event\Model\Category"]),
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
