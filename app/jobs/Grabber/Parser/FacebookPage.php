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
		$venueObj = \Models\Venue::findFirst(['fb_uid = "' . $data['id'] . '"']);

		if (!$venueObj) {
		//if (!\Models\Page::findFirst(['fb_uid = "' . $data['id'] . '"'])) { 
print_r($data['name']);
print_r("\n\r");		
die();
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
		       	$locExists = $locations -> createOnChange($data['location']);
		        if ($locExists) {
					$newPage['location_id'] = $locExists -> id;
				}
			}
						
			$page = new \Models\Page();
			$page -> assign($newPage);
			if ($page -> save()) {
				return $page -> id;
			} else {
				return false;
			}
		} else {
print_r($venueObj -> fb_uid . " venue existenz\n\r");
			
			if (isset($data['location'])) {
				$locations = new \Models\Location();
				$locExists = $locations -> createOnChange(get_object_vars($data['location']));
				if ($locExists) {
print_r($locExists -> id . ": new location\n\r");
					$venueObj -> location_id = $locExists -> id;
					if (!$venueObj -> update()) {
						print_r($venueObj -> fb_uid . ": ooops, venue not updated\n\r");
					} else {
print_r($venueObj -> fb_uid . ": updated\n\r");
					}
				}
			}
		}
	}
}