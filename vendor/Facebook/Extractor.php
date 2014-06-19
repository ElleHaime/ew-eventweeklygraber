<?php

namespace Vendor\Facebook;

use \Vendor\Facebook\FacebookApiException;

class Extractor
{
    private $facebook;

    public function __construct($dependencyInjector = null)
    {
        if (!is_null($dependencyInjector)) {
            $appCfg = $dependencyInjector -> get('config');
            $config = ['appId' => $appCfg -> facebook -> appId,
                       'secret' => $appCfg -> facebook -> appSecret];

            $this -> facebook = new \Vendor\Facebook\Facebook($config);
        } else {
            throw new \Exception('Facebook error: appKey and appSecret are missed');
        }            
    }

    public function getQueriesScope()
    {
        $timelimit = strtotime(date('Y-m-d H:i:s', strtotime('today -1 minute')));
        
        $queries = array(
            array(
                'order' => 1,
                'name' => 'user_event',
                'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
                    FROM event
                    WHERE creator = $userUid
            			AND NOT (eid = 532851033479333)
                     	AND start_time > ' . $timelimit . '
                    ORDER BY eid',
                'type' => 'final',
                'patterns' => array('/\$userUid/')
            ),
        	array(
        		'order' => 8,
        		'name' => 'user_page_uid',
        		'query' => 'SELECT page_id
        			FROM page_admin
        			WHERE uid = $userUid',
        		'type' => 'prepare',
        		'patterns' => array('/\$userUid/')
        	),
        	array(
        		'order' => 9,
        		'name' => 'user_page_event',
        		'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        				FROM event
        				WHERE (creator IN ($userPageUid) OR creator IN($userUid))
        					AND NOT (eid = 532851033479333)
        					AND start_time > ' . $timelimit . '
        				ORDER BY eid',
        		'type' => 'final',
        		'patterns' => array('/\$userPageUid/', '/\$userUid/')
        	),
        	array(
        		'order' => 6,
        		'name' => 'user_going_eid',
        		'query' => 'SELECT eid
        			FROM event_member
        			WHERE uid  = $userUid
        				AND rsvp_status = "attending"
        			ORDER BY start_time',
        		'type' => 'prepare',
        		'patterns' => array('/\$userUid/')
        	),
        	array(
        		'order' => 7,
        		'name' => 'user_going_event',
        		'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        			FROM event
        			WHERE eid IN ($userEventsUid)
        				AND NOT (eid = 532851033479333)
        				AND start_time > ' . $timelimit . '
        			ORDER BY eid',
        		'type' => 'final',
        		'patterns' => array('/\$userEventsUid/')
        	),
        	array(
        		'order' => 10,
        		'name' => 'page_uid',
        		'query' => 'SELECT page_id
        			FROM page_fan
        			WHERE uid = $userUid',
        		'type' => 'prepare',
        		'patterns' => array('/\$userUid/')
        	),
        	array(
        		'order' => 11,
        		'name' => 'page_event',
        		'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        			FROM event
        			WHERE creator IN ($pagesUid)
        				AND NOT (creator  = $userUid)
        				AND NOT (eid = 532851033479333)
        				AND start_time > ' . $timelimit . '
        			ORDER BY eid',
        		'type' => 'final',
        		'patterns' => array('/\$pagesUid/', '/\$userUid/')
        	),        		        		
            array(
                'order' => 2,
                'name' => 'friend_uid',
                'query' => 'SELECT uid2
              		FROM friend 
              		WHERE uid1 = $userUid',
                'type' => 'prepare',
                'patterns' => array('/\$userUid/')
            ),
            array(
                'order' => 3,
                'name' => 'friend_event',
                'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
                    FROM event
                  	WHERE creator IN ($friendsUid)
            		  	AND NOT (eid = 532851033479333)
                      	AND start_time > ' . $timelimit . ' 
                  ORDER BY eid',
                'type' => 'final',
                'patterns' => array('/\$friendsUid/')
            ),
            array(
                'order' => 4,
                'name' => 'friend_going_eid',
                'query' => 'SELECT eid
              		FROM event_member 
              		WHERE uid IN($friendsUid)
                		AND rsvp_status = "attending"
            		ORDER BY start_time',
                'type' => 'prepare',
                'patterns' => array('/\$friendsUid/')
            ),

            array(
                'order' => 5,
                'name' => 'friend_going_event',
                'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
                    FROM event
                    WHERE eid IN ($eventsUid) 
            			AND NOT (eid = 532851033479333)
                      	AND start_time > ' . $timelimit . ' 
                    ORDER BY eid',
                'type' => 'final',
                'patterns' => array('/\$eventsUid/')
            ),
       );

        return $queries;
    }

    public function getCurlFQL($query, $accessToken)
    {
        $url = 'https://api.facebook.com/method/fql.query?query=' . rawurlencode($query) . 
               '&access_token=' . $accessToken;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return new \SimpleXMLElement($response);
    }
}
