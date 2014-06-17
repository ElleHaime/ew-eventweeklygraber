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
                    WHERE eid IN (SELECT eid FROM event_member WHERE uid=$userUid)
                    	AND creator = $userUid
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
        		'patterns' => array('/\$userUid/', '/\$userPageUid/')
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
        			WHERE (eid IN ($userEventsUid) OR creator IN($userUid))
        				AND NOT (eid = 532851033479333)
        				AND start_time > ' . $timelimit . '
        			ORDER BY eid',
        		'type' => 'final',
        		'patterns' => array('/\$userUid/', '/\$userEventsUid/')
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
        			WHERE (creator IN ($pagesUid) OR creator IN($userUid))
        				AND NOT (eid = 532851033479333)
        				AND start_time > ' . $timelimit . '
        			ORDER BY eid',
        		'type' => 'final',
        		'patterns' => array('/\$userUid/', '/\$pagesUid/')
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
                  	WHERE (creator IN ($friendsUid) OR creator IN($userUid))
            		  	AND NOT (eid = 532851033479333)
                      	AND start_time > ' . $timelimit . ' 
                  ORDER BY eid',
                'type' => 'final',
                'patterns' => array('/\$userUid/', '/\$friendsUid/')
            ),
            array(
                'order' => 4,
                'name' => 'friend_going_eid',
                'query' => 'SELECT eid
              FROM event_member 
              WHERE uid IN($friendsUid)
                AND rsvp_status = "attending"',
                'type' => 'prepare',
                'patterns' => array('/\$friendsUid/')
            ),

            array(
                'order' => 5,
                'name' => 'friend_going_event',
                'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
                    FROM event
                    WHERE (eid IN ($eventsUid) OR creator IN($userUid))
            			AND NOT (eid = 532851033479333)
                      	AND start_time > ' . $timelimit . ' 
                    ORDER BY eid',
                'type' => 'final',
                'patterns' => array('/\$userUid/', '/\$eventsUid/')
            ),
       );
        
        /*$queries = array(
        		array(
        				'order' => 1,
        				'name' => 'user_event',
        				'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        				FROM event
        				WHERE eid IN (SELECT eid FROM event_member WHERE uid=$userUid)
        				AND creator = $userUid
        				AND start_time > ' . $timelimit . '
        				ORDER BY eid',
        				'type' => 'final',
        				'start' => false,
        				'limit' => false,
        				'patterns' => array('/\$userUid/')
        		),
        		array(
        				'order' => 8,
        				'name' => 'user_page_uid',
        				'query' => 'SELECT page_id
        				FROM page_admin
        				WHERE uid = $userUid',
        				'type' => 'prepare',
        				'start' => false,
        				'limit' => false,
        				'patterns' => array('/\$userUid/')
        		),
        		array(
        				'order' => 9,
        				'name' => 'user_page_event',
        				'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        				FROM event
        				WHERE creator IN ($userPageUid)
        				AND start_time > ' . $timelimit . '
        				ORDER BY eid
        				LIMIT $start, $lim',
        				'type' => 'final',
        				'start' => 0,
        				'limit' => 50,
        				'patterns' => array('/\$start/',
        						'/\$lim/',
        						'/\$userPageUid/')
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
        				'start' => false,
        				'limit' => false,
        				'patterns' => array('/\$userUid/')
        		),
        		array(
        				'order' => 7,
        				'name' => 'user_going_event',
        				'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        				FROM event
        				WHERE eid IN ($userEventsUid)
        				AND creator != $userUid
        				AND start_time > ' . $timelimit . '
        				ORDER BY eid
        				LIMIT $start, $lim',
        				'type' => 'final',
        				'start' => 0,
        				'limit' => 50,
        				'patterns' => array('/\$start/',
        						'/\$lim/',
        						'/\$userUid/',
        						'/\$userEventsUid/')
        		),
        		array(
        				'order' => 10,
        				'name' => 'page_uid',
        				'query' => 'SELECT page_id
        				FROM page_fan
        				WHERE uid = $userUid',
        				'type' => 'prepare',
        				'start' => false,
        				'limit' => false,
        				'patterns' => array('/\$userUid/')
        		),
        		array(
        				'order' => 11,
        				'name' => 'page_event',
        				'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        				FROM event
        				WHERE creator IN ($pageUid)
        				AND start_time > ' . $timelimit . '
        				ORDER BY eid
        				LIMIT $start, $lim',
        				'type' => 'final',
        				'start' => 0,
        				'limit' => 50,
        				'patterns' => array('/\$start/',
        						'/\$lim/',
        						'/\$userUid/',
        						'/\$pageUid/')
        		),
        		array(
        				'order' => 2,
        				'name' => 'friend_uid',
        				'query' => 'SELECT uid2
        				FROM friend
        				WHERE uid1 = $userUid',
        				'type' => 'prepare',
        				'start' => false,
        				'limit' => false,
        				'patterns' => array('/\$userUid/')
        		),
        		array(
        				'order' => 3,
        				'name' => 'friend_event',
        				'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        				FROM event
        				WHERE creator IN ($friendsUid)
        				AND start_time > ' . $timelimit . '
        				ORDER BY eid
        				LIMIT $start, $lim',
        				'type' => 'final',
        				'start' => 0,
        				'limit' => 50,
        				'patterns' => array('/\$start/',
        						'/\$lim/',
        						'/\$userUid/',
        						'/\$friendsUid/')
        		),
        		array(
        				'order' => 4,
        				'name' => 'friend_going_eid',
        				'query' => 'SELECT eid
        				FROM event_member
        				WHERE uid IN($friendsUid)
        				AND rsvp_status = "attending"',
        				'type' => 'prepare',
        				'start' => false,
        				'limit' => false,
        				'patterns' => array('/\$friendsUid/')
        		),
        
        		array(
        				'order' => 5,
        				'name' => 'friend_going_event',
        				'query' => 'SELECT eid, name, description, location, venue, pic_big, pic_cover, ticket_uri, creator, start_time, end_time
        				FROM event
        				WHERE eid IN ($eventsUid)
        				AND creator != $userUid
        				AND start_time > ' . $timelimit . '
        				ORDER BY eid
        				LIMIT $start, $lim',
        				'type' => 'final',
        				'start' => 0,
        				'limit' => 50,
        				'patterns' => array('/\$start/',
        						'/\$lim/',
        						'/\$userUid/',
        						'/\$eventsUid/')
        		),
        );
        */

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
