<?php

namespace Library;

class Model extends \Phalcon\Mvc\Model
{
	public function initialize()
    {
        $this -> setReadConnectionService('dbMaster');
        $this -> setWriteConnectionService('dbMaster');
    }
    
    public function transferInShards($relationName, $oldObject, $parentId)
    {
    	if ($current = $oldObject -> $relationName) {
    
    		foreach ($current as $obj) {
    			$obj -> event_id = $parentId;
    			$obj -> update();
    		}
    	}
    
    	return;
    }
    
    public function transferBetweenShards($relationName, $oldObject, $parentId)
    {
    	if ($current = $oldObject -> $relationName) {
    
    		foreach ($current as $obj) {
    			$objNew = clone $obj;
    			unset($objNew -> id);
    				
    			$objNew -> event_id = $parentId;
    			$objNew -> setShardById($parentId);
    			if ($objNew -> save()) {
    				$obj -> setShardById($obj -> event_id);
    				$obj -> delete();
    			}
    		}
    	}
    
    	return;
    }
}