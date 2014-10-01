<?php

namespace Jobs\Grabber\Parser;

use Models\EventTag,
	Models\Tag,
	Models\Event;

class Eventbrite
{
	public function __construct(\Phalcon\DI $dependencyInjector)
	{
        $this -> config = $dependencyInjector -> get('config');
	}
	
	
	public function run(\AMQPEnvelope $data)
	{
		error_reporting(E_ALL & ~E_NOTICE);
		
		$msg = unserialize($data -> getBody());
		$ev = $msg['item'];
print_r($ev);
die();		
		if (!Event::findFirst('eb_uid = "' . $ev['id'] . '"'))
		{
//print_r("new event \n\r");	        	
			$result = array();

			$result['eb_uid'] = $ev['id'];
			$result['description'] = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $ev['description']);
			$result['name'] = $ev['name']['text'];
			
			if (!empty($ev['ticket_classes'])) {
				
			}
			
		}
		
	}
}
