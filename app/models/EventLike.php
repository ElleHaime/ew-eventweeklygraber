<?php 

namespace Models;

class EventLike extends \Library\Model
{
	public $id;
    public $event_id;
    public $member_id;
    public $status;
	
	public function initialize()
	{
		parent::initialize();		
		
        $this->belongsTo('event_id', '\Models\Event', 'id', array('alias' => 'memberlike'));
        $this->belongsTo('member_id', '\Models\Member', 'id', array('alias' => 'event_like'));
    }
    
    public function getLikedEventsCount($uId)
    {
    	if ($uId) {
    		$query = new \Phalcon\Mvc\Model\Query("SELECT Models\Event.id, Models\Event.fb_uid
								    				FROM Models\Event
								    					LEFT JOIN Models\EventLike ON Models\Event.id = Models\EventLike.event_id
								    				WHERE Models\Event.deleted = 0
									    				AND Models\Event.event_status = 1
									    				AND Models\Event.start_date > '" . date('Y-m-d H:i:s', strtotime('today -1 minute')) . "'
									    				AND Models\EventLike.status = 1
									    				AND Models\EventLike.member_id = " . $uId, $this -> getDI());
    		$event = $query -> execute();
    
    		return $event;
    	} else {
    		return 0;
    	}
    }
    
    public function deleteEventLiked($eventId)
    {
//     	$events = self::find(['event_id = "' . $eventId . '"']);
//     	if ($events) {
//     		foreach ($events as $obj) {
//     			//$obj -> delete();
//     		}
//     	}

//     	$result = $this -> getReadConnection() -> query("SELECT * FROM event_like WHERE event_id = '" . $eventId . "'");
//     	$result -> setFetchMode(\Phalcon\Db::FETCH_OBJ);
//     	while ($event = $result -> fetch()) {
//     		$e = self::findFirst($event -> id);
//     	}

     	$this -> getReadConnection() -> query("DELETE FROM " . $this -> getSource() . " WHERE event_id = '" . $eventId . "'");
//     	$this -> getReadConnection() -> query("SELECT * FROM " . $this -> getSource() . " WHERE event_id = '" . $eventId . "'");
    	
    	return;
    }
}