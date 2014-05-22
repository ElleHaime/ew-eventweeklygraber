<?php

namespace Tasks;

use \Vendor\Facebook\Extractor,
	\Queue\Producer\Producer;


class harvesterTask extends \Phalcon\CLI\Task
{
	protected $fb;
	protected $queue;


    public function testAction(array $args)
    {

        $this -> queue = new Producer();
        $this -> queue -> connect(['host' => $this -> config -> queue -> host,
                                   'port' => $this -> config -> queue -> port,
                                   'login' => $this -> config -> queue -> login,
                                   'password' => $this -> config -> queue -> password,
                                   'exchangeName' => $this -> config -> queue -> harvester -> exchange,
                                   'exchangeType' => $this -> config -> queue -> harvester -> type,
                                   'routing_key' => $this -> config -> queue -> harvester -> routing_key
                                  ]);
        $this -> queue -> setExchange();
        print_r($this -> queue);
        for ($i = 0; $i < 1000; $i++) {
            echo ".";
            $this -> queue -> publish('Element #' . $i . ' published');
        }
        print_r("\n\rready");
    }


	public function harvestAction(array $args)
	{
		$this -> fb = new Extractor($this -> getDi());

		$this -> queue = new Producer();
		$this -> queue -> connect(['host' => $this -> config -> queue -> host,
								   'port' => $this -> config -> queue -> port,
								   'login' => $this -> config -> queue -> login,
								   'password' => $this -> config -> queue -> password,
								   'exchangeName' => $this -> config -> queue -> harvester -> exchange,
                                   'exchangeType' => $this -> config -> queue -> harvester -> type,
								   'routing_key' => $this -> config -> queue -> harvester -> routing_key
								  ]);
		$this -> queue -> setExchange();
        $queries = $this -> fb -> getQueriesScope();

        foreach ($queries as $key => $query) {

            if ($query['name'] == 'user_event') {
                $replacements = array($args[1]);
                $result = $this -> fb -> getCurlFQL(preg_replace($query['patterns'], $replacements, $query['query']), 
                									$args[0]);
//print_r($result);                
                if (count($result -> event) > 0) {
					$this -> publishToBroker($result, $args, $query['name']);
				}

                continue;
            }

            if ($query['name'] == 'friend_uid') {
                $replacements = array($args[1]);
                $result = $this -> fb -> getCurlFQL(preg_replace($query['patterns'], $replacements, $query['query']), 
                									$args[0]);
//print_r($result);                
                if (count($result -> friend_info) > 0) {
                	foreach ($result -> friend_info as $friend) {
                		$friendsUid[] = json_decode(json_encode($friend), true)['uid2'];
                	}
                }
                continue;
            }

            if ($query['name'] == 'friend_event' && isset($friendsUid) && !empty($friendsUid)) {
                $start = $query['start'];
                $limit = $query['limit'];
                $fUids = implode(',', $friendsUid);

                do {
                    $replacements = array($start, $limit, $args[1], $fUids);
                    $result = $this -> fb -> getCurlFQL(preg_replace($query['patterns'], $replacements, $query['query']), 
                    								$args[0]);
//print_r($result);               
                    if (count($result -> event) > 0) {
						$this -> publishToBroker($result, $args, $query['name']);

                        if (count($result -> event) < (int)$limit) {
                            $start = false;
                        } else {
                            $start = $start + $limit;
                        }
                    } else {
                        $start = false;
                    }
                } while ($start !== false); 

                continue;
            }

            if ($query['name'] == 'friend_going_eid' && !empty($friendsUid)) {
                $replacements = array(implode(',', $friendsUid));
                $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                $result = $this -> fb -> getCurlFQL($fql, $args[0]);
//print_r($result);
                if (count($result -> event_member) > 0) {
                    foreach ($result -> event_member as $event_friend) {
                        $friendsGoingUid[] = json_decode(json_encode($event_friend), true)['eid'];
                    }
                }
                continue;
            }

            if ($query['name'] == 'friend_going_event' && isset($friendsGoingUid) && !empty($friendsGoingUid)) {
                $start = $query['start'];
                $limit = $query['limit'];
                $eChunked = array_chunk($friendsGoingUid, 100);
                $currentChunk = 0;

                do {
                    $eids = implode(',', $eChunked[$currentChunk]);

                    $replacements = array($start, $limit, $args[1], $eids);
                    $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                    $result = $this -> fb -> getCurlFQL($fql, $args[0]);
//print_r($result);
                    if (count($result -> event) > 0) {

						$this -> publishToBroker($result, $args, $query['name']);

                        if (count($result -> event) < (int)$limit) {
                            if ((count($eChunked) - 1) > $currentChunk) {
                                $currentChunk++;
                                $start = 0;
                            } else {
                                $start = false;
                                $currentChunk = 0;
                            }
                        } else {
                            $start = $start + $limit;
                        }
                    } else {
                        if ((count($eChunked) - 1) > $currentChunk) {
                            $currentChunk++;
                            $start = 0;
                        } else {
                            $start = false;
                            $currentChunk = 0;
                        }
                    }
                } while ($start !== false);

                continue;
            }

            if ($query['name'] == 'user_going_eid') {
                $replacements = array($args[1]);
                $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                $result = $this -> fb -> getCurlFQL($fql, $args[0]);
//print_r($result);                
                if (count($result -> event_member) > 0) {
                    foreach ($result -> event_member as $event_user) {
                        $userGoingUid[] = json_decode(json_encode($event_user), true)['eid'];
                    }
                }
                continue;
            }

            if ($query['name'] == 'user_going_event' && isset($userGoingUid) && !empty($userGoingUid)) {
                $start = $query['start'];
                $limit = $query['limit'];
                $eids = implode(',', $userGoingUid);

                do {
                    $replacements = array($start, $limit, $args[1], $eids);
                    $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                    $result = $this -> fb -> getCurlFQL($fql, $args[0]);
//print_r($result);
                    if (count($result -> event) > 0) {
                        $this -> publishToBroker($result, $args, $query['name']);

                        if (count($result -> event) < (int)$limit) {
                            $start = false;
                        } else {
                            $start = $start + $limit;
                        }
                    } else {
                        $start = false;
                    }
                } while ($start !== false);

                continue;
            }

            if ($query['name'] == 'user_page_uid') {
                $replacements = array($args[1]);
                $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                $result = $this -> fb -> getCurlFQL($fql, $args[0]);
//print_r($result);
                if (count($result -> page_admin) > 0) {
                    foreach ($result -> page_admin as $user_page) {
                        $userPagesUid[] = json_decode(json_encode($user_page), true)['page_id'];
                    }
                }
                continue;
            }


            if ($query['name'] == 'user_page_event' && isset($userPagesUid) && !empty($userPagesUid)) {
                $start = $query['start'];
                $limit = $query['limit'];
                $upUids = implode(',', $userPagesUid);

                do {
                    $replacements = array($start, $limit, $upUids);
                    $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                    $result = $this -> fb -> getCurlFQL($fql, $args[0]);
                    if (count($result -> event) > 0) {
                        $this -> publishToBroker($result, $args, $query['name']);

                        if (count($result -> event) < (int)$limit) {
                            $start = false;
                        } else {
                            $start = $start + $limit;
                        }
                    } else {
                        $start = false;
                    }
                } while ($start !== false);

                continue;
            }


            if ($query['name'] == 'page_uid') {
                $replacements = array($args[1]);
                $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                $result = $this -> fb -> getCurlFQL($fql, $args[0]);
//print_r($result);
                if (count($result -> page_fan) > 0) {
                    foreach ($result -> page_fan as $page) {
                        $pagesUid[] = json_decode(json_encode($page), true)['page_id'];
                    }
                }
                continue;
            }

            if ($query['name'] == 'page_event' && isset($pagesUid) && !empty($pagesUid)) {

                $start = $query['start'];
                $limit = $query['limit'];
                $pUids = implode(',', $pagesUid);

                do {
                    $replacements = array($start, $limit, $args[1], $pUids);
                    $fql = preg_replace($query['patterns'], $replacements, $query['query']);
                    $result = $this -> fb -> getCurlFQL($fql, $args[0]);
//print_r($result);
                    if (count($result -> event) > 0) {
                        $this -> publishToBroker($result, $args, $query['name']);

                        if (count($result -> event) < (int)$limit) {
                            $start = false;
                        } else {
                            $start = $start + $limit;
                        }
                    } else {
                        $start = false;
                    }
                } while ($start !== false);

                continue;
            }
        }

        //print_r("done \n\r");
	}

	protected function publishToBroker($result, $args, $resultType)
	{
        foreach ($result as $event) {
        	$data = ['args' => $args,
        			 'item' => json_decode(json_encode($event), true),
        			 'type' => $resultType];
        	$this -> queue -> publish(serialize($data));
        }
	}
}
