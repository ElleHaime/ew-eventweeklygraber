<?php

namespace Jobs\Grabber\Parser;

use \Models\Page as Page;

class FacebookPage
{
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
        $this -> config = $dependencyInjector -> get('config');
	}

	public function run(\AMQPEnvelope $item)
	{
		$data = unserialize($item -> getBody());

		if (!\Models\Page::findFirst(['fb_uid = "' . $data['id'] . '"'])) 
		{
print_r($data);
print_r("\n\r");		
			$newPage = [];
			$newPage['fb_uid'] = $data['id'];
			if (isset($data['username'])) {
				$newPage['fb_uname'] = $data['username'];
			}
			$newPage['name'] = $data['name'];
			$newPage['link'] = $data['link'];
			if (isset($data['category'])) {
				$newPage['category'] = $data['category'];
			}
			if (isset($data['phone'])) {
				$newPage['phone'] = $data['phone'];
			}
			if (isset($data['website'])) {
				$newPage['site'] = $data['website'];
			}
			if (isset($data['likes'])) {
				$newPage['likes'] = $data['likes'];
			}
			$description = '';
			if (isset($data['about'])) {
				$description = $data['about'];	
			} elseif (isset($data['description'])) {
				$description = $data['description'];
			} elseif (isset($data['company_overview'])) {
				$description = $data['company_overview'];
			}
			$newPage['description'] = $description;
						
			
			if (isset($data['location'])) {
				$locations = new \Models\Location();
				$arguments = [];
				
				if (isset($data['location'] -> latitude) && isset($data['location'] -> longitude)) {
					$arguments['latitude'] = $data['location'] -> latitude;
					$arguments['longitude'] = $data['location'] -> longitude; 
				}
				if (isset($data['city'])) {
					$arguments['city'] = $data['city'];
				}
				if (isset($data['country'])) {
					$arguments['country'] = $data['country'];
				}

				if (!empty($arguments)) {
					$locations = new \Models\Location();
			       	$locExists = $locations -> createOnChange($arguments);
			        if ($locExists) {
						$newPage['location_id'] = $locExists -> id;
					}
				}
			}
						
			$page = new \Models\Page();
			$page -> assign($newPage);
			if ($page -> save()) {
				return $page -> id;
			} else {
				return false;
			}
		}
	}
}