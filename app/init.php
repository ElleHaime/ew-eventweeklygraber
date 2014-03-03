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

				['method' => 'registerDirs', 
				 'arguments' => [
					['type' => 'parameter', 
					 'value' => [
						'models' => APPLICATION_PATH . '/app/models',
						'library' => APPLICATION_PATH . '/app/library',
						'tasks' => APPLICATION_PATH . '/app/tasks',
						'jobs' => APPLICATION_PATH . '/app/jobs',
						'vendor' => APPLICATION_PATH . '/vendor']
					]
				 ]
				],

				['method' => 'registerNamespaces',
				 'arguments' => [
				 	['type' => 'parameter', 
				 	 'value' => [
						'Library' => APPLICATION_PATH . '/app/library',
						'Queue' => APPLICATION_PATH . '/app/library/Queue',
						'Queue\Producer' => APPLICATION_PATH . '/app/library/Queue/Producer',
						'Queue\Consumer' => APPLICATION_PATH . '/app/library/Queue/Consumer',
						'Categoryzator' => APPLICATION_PATH . '/vendor/Categoryzator/',
						'Tasks' => APPLICATION_PATH . '/app/tasks',
						'Jobs' => APPLICATION_PATH . '/app/jobs',
						'Jobs\Parser' => APPLICATION_PATH . '/app/jobs/Parser',
						'Vendor' => APPLICATION_PATH . '/vendor',
						'Vendor\Facebook' => APPLICATION_PATH . '/vendor/Facebook',
						'Models' => APPLICATION_PATH . '/app/models']
					]
				 ]
				],
				['method' => 'register'],
			]
		   ]);
$di -> get('loader');

if(is_readable(APPLICATION_PATH . '/config/config.php')) {
	include APPLICATION_PATH . '/config/config.php';
	$config = new \Phalcon\Config($cfg);

	$di -> set('config', $config);
}

$di -> set('db',
	function () use ($config) {
		$eventsManager = new \Phalcon\Events\Manager();

		$adapter = '\Phalcon\Db\Adapter\Pdo\\' . $config -> database -> adapter;

		$connection = new $adapter(
			array('host' => $config -> database -> host,
				  'username' => $config -> database -> username,
				  'password' => $config -> database -> password,
				  'dbname' => $config -> database -> dbname,
                  'charset' => $config -> database -> charset
			)
		);
        $connection -> setEventsManager($eventsManager);

		return $connection;
	} 
);


$di -> set('dispatcher', [
		'className' => '\Phalcon\CLI\Dispatcher',
		'calls' => [
			['method' => 'setDefaultNamespace', 
			 'arguments' => [
				['type' => 'parameter', 'value' => 'Tasks'],
			]],
			['method' => 'setDefaultTask', 
			 'arguments' => [
				['type' => 'parameter', 'value' => 'observer'],
			]],
			['method' => 'setDefaultAction', 
			 'arguments' => [
				['type' => 'parameter', 'value' => 'observe'],
			]],
		],
	]
);

$di -> set('geo', function() use ($di) {
	return new \Library\Geo($di);
});

$console = new ConsoleApp();
$console -> setDI($di);
$di -> setShared('console', $console);

$frontCache = new \Phalcon\Cache\Frontend\Data(['lifetime' => $config -> cache -> lifetime]);
$cache = new \Phalcon\Cache\Backend\Memcache($frontCache, 
											 ['host' => $config -> cache -> host,
											  'port' => $config -> cache -> port,
											  'persistent' => $config -> cache -> persistent]);
$di -> set('cacheData', $cache);

$arguments = array();

define('CURRENT_TASK', null);
define('CURRENT_ACTION', null);

