<?php

use Phalcon\DI\FactoryDefault\CLI as CliDI,
	Phalcon\CLI\Console as ConsoleApp;

define('VERSION', '1.0.0');
defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(dirname(__FILE__)));

$di = new CliDI();

$di -> set('router', array(
	'className' => '\Phalcon\CLI\Router',
));

$di -> set('loader', [
			'className' => '\Phalcon\Loader',
			'calls' => [
				['method' => 'registerNamespaces',
				 'arguments' => [
				 	['type' => 'parameter', 
				 	 'value' => [
						'Library' => APPLICATION_PATH . '/app/library',
						'Library\Sitemap' => APPLICATION_PATH . '/app/library/Sitemap',
						'Queue' => APPLICATION_PATH . '/app/library/Queue',
						'Queue\Producer' => APPLICATION_PATH . '/app/library/Queue/Producer',
						'Queue\Consumer' => APPLICATION_PATH . '/app/library/Queue/Consumer',
						'Categoryzator' => APPLICATION_PATH . '/vendor/Categoryzator/',
						'Tasks' => APPLICATION_PATH . '/app/tasks',
						'Tasks\Eventbrite' => APPLICATION_PATH . '/app/tasks/Grabber/Eventbrite',
				 		'Tasks\Facebook' => APPLICATION_PATH . '/app/tasks/Grabber/Facebook',
				 		'Tasks\Facebook\User' => APPLICATION_PATH . '/app/tasks/Grabber/Facebook/User',
				 		'Tasks\Facebook\Custom' => APPLICATION_PATH . '/app/tasks/Grabber/Facebook/Custom',
						'Tasks\Facebook\Creator' => APPLICATION_PATH . '/app/tasks/Grabber/Facebook/Creator',
						'Tasks\Categorization' => APPLICATION_PATH . '/app/tasks/Categorization',
						'Tasks\Synchronization' => APPLICATION_PATH . '/app/tasks/Synchronization',
						'Tasks\Seo' => APPLICATION_PATH . '/app/tasks/Seo',
						'Tasks\Seo\Sitemap' => APPLICATION_PATH . '/app/tasks/Seo/Sitemap',
						'Jobs\Grabber' => APPLICATION_PATH . '/app/jobs/Grabber',
						'Jobs\Grabber\Parser' => APPLICATION_PATH . '/app/jobs/Grabber/Parser',
						'Jobs\Grabber\Sync' => APPLICATION_PATH . '/app/jobs/Grabber/Sync',
						'Jobs\Grabber\Clean' => APPLICATION_PATH . '/app/jobs/Grabber/Clean',
						'Jobs\Grabber\Categorize' => APPLICATION_PATH . '/app/jobs/Grabber/Categorize',
						'Jobs\Seo' => APPLICATION_PATH . '/app/jobs/Seo',
						'Jobs\Seo\Sitemap' => APPLICATION_PATH . '/app/jobs/Seo/Sitemap',
						'Vendor' => APPLICATION_PATH . '/vendor',
						'Vendor\Facebook' => APPLICATION_PATH . '/vendor/Facebook',
				 		'Vendor\FacebookGraph' => APPLICATION_PATH . '/vendor/FacebookGraph',
				 		'Vendor\Eventbrite' => APPLICATION_PATH . '/vendor/Eventbrite',
						'Models' => APPLICATION_PATH . '/app/models',
				 		'Sharding' => APPLICATION_PATH . '/vendor/tieste/sharding/lib/Sharding',
                        'Engine' => APPLICATION_PATH . '/vendor/tieste/phalcon-core/lib/Engine',
                        'Elastica' => APPLICATION_PATH . '/vendor/ruflin/elastica/lib/Elastica'
						]
					]
				 ]
				],
				['method' => 'register'],
			],
		   ]);

$di -> get('loader');

if(is_readable(APPLICATION_PATH . '/config/config.php')) {
	include APPLICATION_PATH . '/config/config.php';
	$config = new \Phalcon\Config($cfg);

	$di -> set('config', $config);
}

if(is_readable(APPLICATION_PATH . '/config/sharding.php')) {
	include APPLICATION_PATH . '/config/sharding.php';
	$shardingConfig = new \Phalcon\Config($cfg_sharding);

	$di -> set('shardingConfig', $shardingConfig);
}

if(is_readable(APPLICATION_PATH . '/config/shardingService.php')) {
	include APPLICATION_PATH . '/config/shardingService.php';
	$shardingServiceConfig = new \Phalcon\Config($cfg_sharding_service);

	$di -> set('shardingServiceConfig', $shardingServiceConfig);
}

$di -> set('dbMaster',
	function () use ($config) {
		$eventsManager = new \Phalcon\Events\Manager();

		$adapter = '\Phalcon\Db\Adapter\Pdo\\' . $config -> database -> adapter;

		$connection = new $adapter(
			array('host' => $config -> database -> host,
				  'username' => $config -> database -> username,
				  'password' => $config -> database -> password,
				  'dbname' => $config -> database -> dbname,
				  'port' => $config -> database -> port,
                  'charset' => $config -> database -> charset,
				  'options' => $config -> database -> options -> toArray()
			)
		);
        $connection -> setEventsManager($eventsManager);

		return $connection;
	} 
);

$di -> set('geo', function() use ($di) {
	return new \Library\Geo($di);
});

//$frontCache = new \Phalcon\Cache\Frontend\Data(['lifetime' => $config -> cache -> lifetime]);
$frontCache = new \Phalcon\Cache\Frontend\None();
$cache = new \Library\Cache\Memcache($frontCache,
		['host' => $config -> cache -> host,
		'port' => $config -> cache -> port,
		'persistent' => $config -> cache -> persistent,
		'prefix' => $config -> database -> dbname]);
$di -> set('cacheData', $cache); 

$di->set('elastic',
    function() use ($config) {
        $config = [
            'index' => $config -> elastic -> index,
            'connections' => $config -> elastic -> connections -> toArray()
        ];

        return new \Engine\Search\Elasticsearch\Client($config);
    }
);

$console = new ConsoleApp();
$console -> setDI($di);
$di -> setShared('console', $console);

$arguments = array();

define('CURRENT_TASK', null);
define('CURRENT_ACTION', null);

