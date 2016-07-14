<?php

namespace Jobs\Grabber\Parser;

use Models\EventImage,
	Models\EventTag,
	Models\EventCategory,
	Models\VenueImage,
	Models\VenueTag,
	Models\VenueCategory,
	Models\Category,
	Models\Tag,
	Library\Utils\SlugUri;


trait Helper
{
	public function saveVenueImage($parser = 'fb', $source, \Models\Venue $venue, $imgType = 'logo', $width = false, $height = false)
	{
		$img = $this -> loadImage($source, $venue, $parser, 'venue', $imgType);
		
		$images = new VenueImage();
		$images -> assign(['venue_id' => $venue -> id, 
						   'image' => $img, 
						   'type' => $imgType])
				-> save();
	}
	
	
	public function saveEventImage($parser = 'fb', $source, \Models\Event $event, $imgType = null, $width = false, $height = false)
	{
        $img = $this -> loadImage($source, $event, $parser, 'event', $imgType);
        
        $images = new EventImage();
        $images -> setShardById($event -> id);
        $images -> assign(['event_id' => $event -> id,
                		   'image' => $img,
                		   'type' => $imgType])
        		-> save();
    }	
    
    
    private function loadImage($source, $object, $parser = 'fb', $objType = 'event', $imgType = null)
    {
    	$prop = $parser . '_uid';
    	
    	if ($parser == 'fb') {
    		$img = $this -> getImageName($source, $object -> name, $imgType);
    	} else {
    		$img = $object -> logo;
    	}
// print_r("\n\r");
// print_r($img);
   	
    	$ch = curl_init($source);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
    	$content = curl_exec($ch);
    	
    	if ($objType == 'event') {
    		if(!empty($object -> start_date)) { 
    			$objDatesName = date('Y', strtotime($object -> start_date)) . '/' 
    							. date('m', strtotime($object -> start_date)) . '/' 
    							. date('d', strtotime($object -> start_date));
    		} else {
    			$objDatesName = 'undated';
    		} 
    		$mainDir = $this -> config -> application -> uploadDir -> $objType . $objDatesName . '/' . $object -> id;
    	} else {
    		$mainDir = $this -> config -> application -> uploadDir -> $objType . $object -> location_id . '/' . $object -> id;
    	}
    	
// print_r("\n\r");
// print_r($mainDir);

    	 
    	if (is_null($imgType) || $imgType == 'logo') {
    		$fDir = $mainDir;
    		$fPath = $mainDir . '/' . $img;
    	} else {
    		$fDir = $mainDir . '/' . $imgType;
    		$fPath = $mainDir . '/' . $imgType . '/' . $img;
    	}
// print_r("\n\r");
// print_r($fPath);
    	
    	if ($content) {
    		if (!is_dir($fDir)) mkdir($fDir, 0777, true);
    		$f = fopen($fPath, 'wb');
    		fwrite($f, $content);
    		fclose($f);
    		chmod($fPath, 0777);
    	}
    	
    	return $img;
    }

    
    public function getImageName($url, $name, $imgType = 'logo')
    {
    	$imgName = $imgExt = '';
    	$ext = explode('.', $url);
    	strpos(end($ext), '?') ? $imgExtenstion = substr(end($ext), 0, strpos(end($ext), '?')) : $imgExtenstion = end($ext);
    	$imgType == 'logo' ? $imgName = SlugUri::slug($name) : $imgName = SlugUri::slug($name) . '-' . time(); 
    	$image = $imgName . '.' . $imgExtenstion;
    	
    	return $image;
    }
    
    
    public function categorizeObject($objectId, $params = [], $objectType = 'event')
    {
    	$Text = new \Categoryzator\Core\Text();
    	foreach ($params as $key => $val) {
    		$Text -> addContent($val);
    	}
        $Text -> returnTag(true);

        $categoryzator = new \Categoryzator\Categoryzator($Text);
        $newText = $categoryzator->analiz(\Categoryzator\Categoryzator::MULTI_CATEGORY);
        $cats = $tags = [];

        foreach ($newText -> category as $key => $c) {
        	$Cat = Category::findFirst('key = \''.$c.'\'');
            if ($Cat) {
            	if ($objectType == 'event') {
	            	$cats = new EventCategory();
	            	$cats -> setShardById($objectId);
	            	$cats -> assign(['category_id' => $Cat->id,
	            					 'event_id' => $objectId]);
            	} elseif ($objectType == 'venue') {
            		$cats = new VenueCategory();
	           		$cats -> assign(['category_id' => $Cat->id,
            						 'venue_id' => $objectId]);
            	}
            	$cats -> save();
            }
        }

        foreach ($newText -> tag as $c) {
        	foreach ($c as $key => $tag) {
            	$Tag = Tag::findFirst('name = \''.$tag.'\'');
                if ($Tag) {
                	if ($objectType == 'event') {
	                	$tags = new EventTag();
	                	$tags -> setShardById($objectId);
	                    $tags -> assign(['tag_id' => $Tag->id,
	                    				 'event_id' => $objectId]);
                	} elseif ($objectType == 'venue') {
                		$tags = new VenueTag();
                		$tags -> assign(['tag_id' => $Tag->id,
          			      				 'venue_id' => $objectId]);
                	}
                    $tags -> save();
                }
            }
        }
        
        return;
    }
    
    
    public function processDates($result, $source)
    {
    	if (isset($source['start_time']) && isset($source['end_time']) && !empty($source['end_time'])) {
    		$result['start_date'] = date('Y-m-d H:i:s', strtotime($source['start_time']));
    		$result['end_date'] = date('Y-m-d H:i:s', strtotime($source['end_time']));

    		if(strtotime($result['start_date']) >= strtotime($result['end_date'])) {
    			$result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));
    		}
    		 
    	} elseif (isset($source['start_time']) && (!isset($source['end_time']) || empty($source['end_time']))) {
    		$result['start_date'] = date('Y-m-d H:i:s', strtotime($source['start_time']));
    		$result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));
    		
    	} elseif (!isset($source['start_time']) && isset($source['end_time'])) {
    		$result['end_date'] = date('Y-m-d H:i:s', strtotime($source['end_time']));
    	}	
		
		return $result;
    }
    
    
    public function prepareText($arg) 
    {
    	return preg_replace('/<a[^>]*>((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.#?=-]*)*\/?)<\/a>/ui', '<a href="$1" target="_blank">$1</a>', $arg);
    	
    }
    
    
    public function addToIndex($eventObj)
    {
    	$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> _di, null, ['adapter' => 'dbMaster']);
    	
    	$indexer = new \Models\Event\Search\Indexer($grid);
    	$indexer -> setDi($this->_di);
    	
//     	if (!$indexer -> existsData($eventObj -> id)) {
	    	if (!$indexer -> addData($eventObj -> id)) {
	    		print_r("ooooooops, not saved to index\n\r");
	    	}
//     	} else {
//     		if (!$indexer -> updateData($eventObj -> id)) {
//     			print_r("ooooooops, not updated in index\n\r");
//     		}
//     	}
    	
    	return;
    }
}