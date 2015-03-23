<?php 

namespace Tasks;

use \Queue\Producer\Producer,
	\Facebook\FacebookSession,
	\Facebook\FacebookRequest;


class graphgrabTask extends \Phalcon\CLI\Task
{
	protected $fbSession;
	
	public function init()
	{
		$appCfg = $this -> getDi() -> get('config');
		try {
			FacebookSession::setDefaultApplication($appCfg -> facebook -> appId, $appCfg -> facebook -> appSecret);
			FacebookSession::enableAppSecretProof();
			$this -> fbSession = FacebookSession::newAppSession();
		} catch(\Exception $e) {
			print_r($e);
		}
	} 
	
	public function harvestAction()
	{
		$this -> init();
		//$query = '/search?q=*&type=place&center=53.3243,-6.251&distance=50000';
		$query = '/search?q=dublin&type=event&access_token=CAACXkwzE0HkBAPOyO8eZAGM2ZAUyE7V1DpRVKGMKEwc9khIW0SfJ8RqMwykTiMO08BgUGrTohB07LjHtg98EZA2bMr2Y7osJcvHmzzy8DURVbAaaSDZCaU7nCx9xGayWs5atuVqN7t9ihw0RUZC8hvpGHTQYSjq7gqS1TQtW8ZCz9hrzt8ZA54EI9v7gzouYxyCaLv9liqETqClTVqZAJzEZA';
		$query = '/776370009048569'; 
		
		$request = new FacebookRequest($this -> fbSession, 'GET', $query);
		$data = $request -> execute() -> getGraphObject() -> asArray();
		print_r(count($data['data']));
		print_r("\n\r");
		print_r($data);
		print_r("\n\r");
		
		die();
	}
}