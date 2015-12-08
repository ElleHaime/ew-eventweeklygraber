<?php

namespace Jobs\Seo;

use Models\Event,
	Models\Cron,
	Models\Location,
	Library\Utils\SlugUri,
	Library\Sitemap\Sitemap,
	Models\Event\Grid\Search\EventSearch;

class Generator
{
	protected $_di;
	protected $_config;
	protected $_smConfig;
	
	
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
		$this -> _config = $dependencyInjector -> get('config');
		$this -> _smConfig = $this -> _config -> sitemap;
		$this -> _di = $dependencyInjector;
	}
	
	
	public function run()
	{
		$locations = Location::find();
		 		
		$generator = new Sitemap($this -> _smConfig -> domain, $this -> _smConfig -> sitemapPath);
		$generator -> setPathIndex($this -> _smConfig -> indexPath);
		$generator -> setUrlSitemap($this -> _smConfig -> sitemapUrl);
		  
		foreach($locations as $loc)
		{
			$eventGrid = new EventSearch(['searchLocationField' => $loc -> id], $this -> _di, null, ['adapter' => 'dbMaster']);
			$eventGrid -> setLimit(100);
			$result = $eventGrid -> getData();

			if ($result) {
				foreach ($result['data'] as $id => $e) {
					$url = SlugUri::slug($e -> name) . '-' . $e -> id;
					$generator -> addItem($url);
				}
			}
		}
		$generator -> createSitemapIndex();
		
		$list = $generator -> getSitemapsList();
		$index = $generator -> getSitemapsIndex();
		
print_r($list); 
print_r($index);
		if (!empty($list)) {
			shell_exec($this -> _smConfig -> shell_path);
		}	
print_r("\n\rready\n\r"); 
die();			
	}
}