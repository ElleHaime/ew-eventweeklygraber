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
    Engine\Filter\SearchFilterInterface as Criteria,
    Engine\Search\Elasticsearch\Filter\AbstractFilter;

/**
 * Class Events.
 *
 * @category   Module
 * @package    Event
 * @subpackage Grid
 */
class Venue extends Grid
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
     * Initialize grid columns
     *
     * @return void
     */
    protected function _initColumns()
    {
        $this->_columns = [
            'id' => new Column\Primary('Id'),
            'name' => new Column\Text('Name', 'name'),
            'location' => new Column\JoinOne("Location", "Models\Event\Model\Location"),
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
                Criteria::COLUMN_NAME   => Criteria::CRITERIA_LIKE,
                'description'           => Criteria::CRITERIA_LIKE,
                'location'              => Criteria::CRITERIA_LIKE,
                'category'              => Criteria::CRITERIA_LIKE,
                'tag'                   => Criteria::CRITERIA_LIKE,
            	'id'          			=> Criteria::CRITERIA_EQ
            ]),
            'id' => new Field\Primary("id"),
            'name' => new Field\Name("Name"),
            'desc' => new Field\Standart("Description", 'description'),
            'location' => new Field\Join("Location", "Models\Event\Model\Location"),
            'category' => new Field\Join("Category", "Models\Event\Model\Category", false, null, ["Models\Event\Model\VenueCategory", "Models\Event\Model\Category"]),
            'tag' => new Field\Join("Tags", "Models\Event\Model\Tag", false, null, ["Models\Event\Model\VenueTag", "Models\Event\Model\Tag"]),
        	'latitude' => new Field\Standart('Latitude', 'latitude', null, Criteria::CRITERIA_EQ),
        	'longitude' => new Field\Standart('Longitude', 'longitude', null, Criteria::CRITERIA_EQ),
        	'address' => new Field\Standart('Address', 'address', null, Criteria::CRITERIA_LIKE),
        	'logo' => new Field\Standart('Logo', 'logo', null, Criteria::CRITERIA_EQ)
        ], null, 'get');
    }
    
    
    public function getParams()
    {
    	return $this -> _params;
    }
}
