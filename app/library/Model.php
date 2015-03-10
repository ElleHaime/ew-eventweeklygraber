<?php

namespace Library;

class Model extends \Phalcon\Mvc\Model
{
	public function initialize()
    {
        $this -> setReadConnectionService('dbMaster');
        $this -> setWriteConnectionService('dbMaster');
    }
}