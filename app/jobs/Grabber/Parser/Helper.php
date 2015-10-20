<?php

namespace Jobs\Grabber\Parser;

use Models\EventImage,
	 Models\EventTag,
	 Models\EventCategory,
	 Models\Category,
	 Models\Tag;

trait Helper
{
	public function saveEventImage($parser = 'fb', $source, \Models\Event $event, $imgType = null, $width = false, $height = false)
	{
		$prop = $parser . '_uid';
		
		if ($parser == 'fb') {
	        $ext = explode('.', $source);
	        if (strpos(end($ext), '?')) {
	            $img = $parser . '_' . $event -> $prop . '.' . substr(end($ext), 0, strpos(end($ext), '?'));
	        } else {
	        	$img = $parser . '_' . $event -> $prop . '.' . end($ext);
	        }
		} else {
			$img = $event -> logo;
		} 

		$ch = curl_init($source);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);

        if (is_null($imgType)) {
            $fDir = $this -> config -> application -> uploadDir . $event -> id;
            $fPath = $this -> config -> application -> uploadDir . $event -> id . '/' . $img;
        } else {
            $fDir = $this -> config -> application -> uploadDir . $event -> id . '/' . $imgType;
            $fPath = $this -> config -> application -> uploadDir . $event -> id . '/' . $imgType . '/' . $img;            
        }
        
        if ($content) {
            if (!is_dir($fDir)) {
                mkdir($fDir, 0777, true);
            }
            $f = fopen($fPath, 'wb');
            fwrite($f, $content);
            fclose($f);
            chmod($fPath, 0777);
        }
        $images = new EventImage();
        $images -> setShardById($event -> id);
        $images -> assign(['event_id' => $event -> id,
                		   'image' => $img,
                		   'type' => $imgType]);
        $images -> save();
    }	
    
    
    public function categorize($event)
    {
    	$Text = new \Categoryzator\Core\Text();
	        if (!empty($event -> name)) {
        	$Text -> addContent($event -> name);
        } 
        if (!empty($event -> description)) {
            $Text -> addContent($event -> description);
        } 
        $Text -> returnTag(true);

        $categoryzator = new \Categoryzator\Categoryzator($Text);
        $newText = $categoryzator->analiz(\Categoryzator\Categoryzator::MULTI_CATEGORY);
        $cats = [];
        $tags = [];

        foreach ($newText->category as $key => $c) {
        	$Cat = Category::findFirst('key = \''.$c.'\'');
            if ($Cat) {
            	$cats = new EventCategory();
            	$cats -> setShardById($event -> id);
            	$cats -> assign(['category_id' => $Cat->id,
            					 'event_id' => $event -> id]);
            	$cats -> save();
            }
        }

        foreach ($newText->tag as $c) {
        	foreach ($c as $key => $tag) {
            	$Tag = Tag::findFirst('name = \''.$tag.'\'');
                if ($Tag) {
                	$tags = new EventTag();
                	$tags -> setShardById($event -> id);
                    $tags -> assign(['tag_id' => $Tag->id,
                    				 'event_id' => $event -> id]);
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
    
    
    public function increaseEventTotal()
    {
    	$total = \Models\Total::findFirst('entity = "event"');
    	$total -> total = $total -> total + 1;
    	$total -> update();
    	
    	return;
    }
    
    
    public function addToIndex($eventObj)
    {
    	$grid = new \Models\Event\Grid\Search\Event(['location' => $eventObj -> location_id], $this -> _di, null, ['adapter' => 'dbMaster']);
    	$indexer = new \Models\Event\Search\Indexer($grid);
    	$indexer -> setDi($this->_di);
    	if (!$indexer -> existsData($eventObj -> id)) {
	    	if (!$indexer -> addData($eventObj -> id)) {
	    		print_r("ooooooops, not saved to index\n\r");
	    	}
    	}
    	
    	return;
    }
}