<?php
	ini_set('html_errors', false);
	function __autoload($class){
		require_once($class.'.class.php');
	}
	require_once('functions.php');


	//$docs = getView('http://voikukka.mobile.metropolia.fi:5984/uutisarkisto','keyphrases');
	
	$d = new db();

	/*
	foreach ($docs as $doc){
		$d->saveKeyphrases($doc['id'],$doc['value']);
	}
	*/

/*
	$docs = getView('http://voikukka.mobile.metropolia.fi:5984/uutisarkisto','notkeyphrases');
	$count = 0;

	foreach ($docs as $doc){
		if (isset($doc['value']['title'])){
			$count++;
			$keyphrases = getKeyPhrases( $doc['value']['title'].' '.$doc['value']['text'] );
			$d->saveKeyphrases($doc['id'],$keyphrases);
		}
		
	}
*/

	function getView($url,$view,$data = false){
		if ($data){
			$url = $url.'/_design/doc/_view/'.$view.'?'.$data;
		} else {
			$url = $url.'/_design/doc/_view/'.$view;
		}

		$d = json_decode( file_get_contents($url) ,true);

		if ($d['rows']){
			return $d['rows'];
		} else {
			return false;
		}
	}
?>