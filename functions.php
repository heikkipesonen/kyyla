<?php
	function scale($ar = array()){
		$result = array();
		$sum = array_sum( array_values($ar) );
		foreach ($ar as $key => $value){
			$result[$key] = $value / $sum;
		}
		return $result;
	}
	
	function multiply($ar = array(), $multiplier = 1){
		$result = array();
		foreach ($ar as $key => $val){
			$result[$key] = $val*$multiplier;
		}
		return $result;
	}

	function getArraySum($arrays){		
		$result = array();
		foreach ($arrays as $array){
			if ($array){
				foreach ($array as $key => $value){
					if (!isset($result[$key])){
						$result[$key] = 0;
					}
					$result[$key] += $value;
				}
			}
		}
		return $result;
	}
	
	function filterSmallValues($array, $limit = 0.01){		
		$result = array();
		foreach ($array as $key => $value){
			if ($value > $limit){
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	function scaleToMax($array,$max = 1){
		if (count($array)>0){
			arsort($array);
			$max = $max / reset($array);
	
			$result = array();
			foreach ($array as $key=>$value){
				$result[$key] = $value*$max;
			}
			return $result;
		} else {
			return false;
		}
		
	}

	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}

	function parseText($text){	
		$result = preg_replace("/<(.|\n)*?>/",' ', $text);
		$result = preg_replace("/[^A-Za-z0-9 ]/", '', $result);
		return $result;
	}

	
	
	function getKeyPhrases($text){
		$text = parseText($text);
		$data = array('text' => $text);

		try{
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,'http://ereading.metropolia.fi/JujuWeb/rest/keyphrase');
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data) );
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			//curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));		
			$result = curl_exec($ch);

			$result = json_decode($result, true);	

		} catch (Exception $e){
			$result = false;
		}

		return $result;	
	}

	function getMedian($arrays){
		$result = array();
		
		foreach ($arrays as $array){
			foreach ($array as $key=>$value){
				if (!isset($result[$key])){
					$result[$key] = array();
				}
				$result[$key][] = $value;
			}	
		}
		foreach ($result as $key => $values){
			$totalValue = array_sum($values);
			$result[$key]=$totalValue/count($values);
		}		
		return $result;
	}
	
	function sortArticleKeyphrases($a,$b){
		return $b['value'] - $a['value'];
	}


	function sortKeyphrases($a,$b){
		if ($a['count'] == $b['count']){
			return $b['value'] - $a['value'];
		}

		return $b['count'] - $a['count'];
	}
?>