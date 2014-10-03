<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag,
	Models\Tag;

trait Helper
{
	public function saveEventImage($parser = 'fb', $source, \Models\Event $event, $imgType = null, $width = false, $height = false)
	{
		$prop = $parser . '_uid';
        $ext = explode('.', $source);
        if (strpos(end($ext), '?')) {
            $img = $parser . '_' . $event -> $prop . '.' . substr(end($ext), 0, strpos(end($ext), '?'));
        } else {
            $img = $parser . '_' . $event -> $prop . '.' . end($ext);
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

        $images = new \Models\EventImage();
        $images -> assign(array(
                'event_id' => $event -> id,
                'image' => $img,
                'type' => $imgType));
        $images -> save();
    }	
    
    
    public function categorize($result)
    {
    	$Text = new \Categoryzator\Core\Text();
	        if (!empty($result['name'])) {
        	$Text -> addContent($result['name']);
        } else {
            $result['name'] = '';
        }
        if (!empty($result['description'])) {
            $Text -> addContent($result['description']);
        } else {
            $result['description'] = '';
        }
        $Text -> returnTag(true);

        $categoryzator = new \Categoryzator\Categoryzator($Text);
        $newText = $categoryzator->analiz(\Categoryzator\Categoryzator::MULTI_CATEGORY);
        $cats = array();
        $tags = array();

        foreach ($newText->category as $key => $c) {
        	$Cat = \Models\Category::findFirst('key = \''.$c.'\'');
            if ($Cat) {
            	$cats[$key] = new \Models\EventCategory();
            	$cats[$key]->category_id = $Cat->id;
            }
        }

        foreach ($newText->tag as $c) {
        	foreach ($c as $key => $tag) {
            	$Tag = Tag::findFirst('name = \''.$tag.'\'');
                if ($Tag) {
                	$tags[$key] = new EventTag();
                    $tags[$key]->tag_id = $Tag->id;
                }
            }
        }
        
        if (!empty($cats)) {
        	return ['cats' => $cats, 'tags' => $tags];
        } else {
        	return false;
        }
    }
    
    
    public function processDates($result)
    {
    	if (isset($result['start_date']) && isset($result['end_date'])) {
			if (isset($result['start_time']) && isset($result['end_time'])) {
				$result['start_date'] = $result['start_date'] . ' ' . $result['start_time'];

				if(strtotime($result['start_date'] . ' ' . $result['start_time']) >= strtotime($result['end_date'] . ' ' . $result['end_time'])) {
					$result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));
				} else {
					$result['end_date'] = $result['end_date'] . ' ' . $result['end_time'];
				}
				unset($result['start_time']);
				unset($result['end_time']);

			} elseif(isset($result['start_time']) && !isset($result['end_time'])) {
				$result['start_date'] = $result['start_date'] . ' ' . $result['start_time'];
				$result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));

				unset($result['start_time']);

			} elseif(!isset($result['start_time']) && isset($result['end_time'])) {
				$result['end_date'] = $result['end_date'] . ' ' . $result['end_time'];
				unset($result['end_time']);
			}
		} elseif (isset($result['start_date']) && !isset($result['end_date'])) {
			$result['end_date'] = date('Y-m-d H:i:s', strtotime($result['start_date'] . ' tomorrow -1 minute'));
			if (isset($result['start_time'])) {
				$result['start_date'] = $result['start_date'] . ' ' . $result['start_time'];    
				unset($result['start_time']);
			} 
			unset($result['start_time']);
		}
		
		return $result;
    }
}