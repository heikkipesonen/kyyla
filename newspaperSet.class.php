<?php
/*
 * 
 * represents a stack of newspapers
 * 
 * for analyyyzisfadjkfasdklf use
 * 
 * 
 */
class newspaperSet{
	private $days = array();

	function __construct(){

	}

	function add($readDay){
		$this->days[] = $readDay;
	}	
	
	function getDates(){
		$result = array();
		foreach ($this->days as $day){
			$result[] = $day->getDate();
		}
		return $result;
	}

	function getDay($rupu_timestamp){
		foreach ($this->days as $tday){
			if ($rupu_timestamp == $tday->getDate()){
				return $tday;
			}
		}
		return false;
	}

	function getWeight($day){	
		if (!get_class($day)=='readDay'){
			$day = $this->getDay($day);
		}
		return $day->getCategoryWeights();
	}

	function getWeightsByDay(){
		$result = array();
		foreach ($this->days as $day){
			$result[$day->getDateString()] = $day->getCategoryWeights();
		}
		krsort($result);		
		return $result;
	}

	function getCategoryWeights(){
		$days = $this->getWeightsByDay();
		$temp = array();
		foreach ($days as $date => $categories){
			foreach ($categories as $category => $weight){
				if (!isset($temp[$category])){
					$temp[$category]=0;
				}
				$temp[$category]+=$weight;
			}
		}
		return $temp;
	}

	function getWeights(){		
		$days = getMedian($this->getWeightsByDay());
		arsort($days);
		return scale($days);
	}

	function getScaledMedianWeights(){		
		return scale($this->getMedianWeights());
	}

	function getCategories(){
		$categories = array();

		foreach ($this->days as $day){
			$names = $day->getCategories();
		}
	}
}
?>