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
    		$relationType = $this -> getRelationType($relationName, $oldObject);
    		
    		if ($relationType == \Phalcon\Mvc\Model\Relation::HAS_ONE) {
    			$this -> moveIn($current, $parentId);
    		} elseif ($relationType == \Phalcon\Mvc\Model\Relation::HAS_MANY) {
	    		foreach ($current as $obj) {
					$this -> moveIn($obj, $parentId);
	    		}
    		} else {
    			print_r("\n\r ... found new relation in shard (type " . $relationType . ") ... \n\r");
    		}
    	}
    
    	return;
    }
    
    public function transferBetweenShards($relationName, $oldObject, $parentId)
    {
    	if ($current = $oldObject -> $relationName) {
    		$relationType = $this -> getRelationType($relationName, $oldObject);
    		
    		if ($relationType == \Phalcon\Mvc\Model\Relation::HAS_ONE) {
    			$this -> moveBetween($current, $parentId); 
    		} elseif ($relationType == \Phalcon\Mvc\Model\Relation::HAS_MANY) {
    			foreach ($current as $obj) {
    				$this -> moveBetween($obj, $parentId);
    			}
    		} else {
    			print_r("\n\r ... found new relation between shards (type " . $relationType . ") ... \n\r");
    		}
    	}
    
    	return;
    }
    
    private function getRelationType($relationName, $oldObject)
    {
    	$relationType = $oldObject -> getModelsManager() -> getRelationByAlias(get_class($oldObject), $relationName) -> getType();
    	
    	return $relationType;
    }
    
    
    private function moveIn($obj, $parentId)
    {
    	$obj -> event_id = $parentId;
    	$obj -> update();
    	
    	return;
    }
    
    
    private function moveBetween($obj, $parentId)
    {
    	$objNew = clone $obj;
    	unset($objNew -> id);
    		
    	$objNew -> event_id = $parentId;
    	$objNew -> setShardById($parentId);
    	if ($objNew -> save()) {
    		$obj -> setShardById($obj -> event_id);
    		$obj -> delete();
    	}
    	
    	return;
    }
    
}