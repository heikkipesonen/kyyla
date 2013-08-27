<?php

class articleEventSet{
	private $articleEvents = array();

	function __construct($set = false){
		if ($set){
			$this->articleEvents = $set;
		}
	}

	function getTimeFrame(){
		$start_time = 0;
		$end_time = 0;

		foreach ($this->articleEvents as $evt){
			if ($start_time == 0){
				$start_time = $evt->getStartTimeStamp();
			} 

			if ($end_time == 0 && $evt->getEndTimeStamp()){
				$end_time = $evt->getEndTimeStamp();
			}

			if ($evt->getStartTimeStamp() < $start_time ){
				$start_time = $evt->getStartTimeStamp();
			}

			if ($evt->getEndTimeStamp() > $end_time){
				$end_time = $evt->getEndTimeStamp();
			}
		}

		return array($start_time,$end_time);
	}

	function hasId($id){
		foreach ($this->articleEvents as $evt){
			if ($evt->getId() == $id){
				return true;
			}
		}
		return false;
	}

	function getId($id){
		foreach ($this->articleEvents as $evt){
			if ($evt->getId() == $id){
				return $evt;
			}
		}
		return false;		
	}

	function add($articleEvent,$checkDuplicateId = false){		
		if (is_array($articleEvent)){
			foreach ($articleEvent as $evt){
				$this->add($evt,$checkDuplicateId);
			}
		} else {			
			if ($checkDuplicateId == true){
				if (!$this->hasId( $articleEvent->getId())){
					$this->articleEvents[] = $articleEvent;					
				} else {
					$e = $this->getId($articleEvent->getId());					
					$e->addTime( $articleEvent->getDuration() );
				}
			} else {
				$this->articleEvents[] = $articleEvent;
			}
		}
	}

	function getEvents(){
		return $this->articleEvents;
	}

	function getDates(){
		$result = array();
		foreach ($this->articleEvents as $evt){
			$result[$evt->getDate()] = 1;
		}
		return array_keys($result);
	}	
	
	function getDay($date){
		if (!is_object($date)){
			$date = new rupu_timestamp($date);
		}
		
		$result = array();
		foreach ($this->articleEvents as $evt){
			if ($evt->getDate() === $date->getDate()){
				$result[] = $evt;
			}
		}
		return $result;
	}

	function getTotalReadTime(){
		return array_sum( array_values($this->getReadTime()));
	}

	function getReadTime(){
		$cat = array();
		foreach ($this->articleEvents as $evt){											
			if (!isset($cat[$evt->getCategory()])){
				$cat[$evt->getCategory()] = 0;
			}

			$cat[$evt->getCategory()] += $evt->getDuration();
		}
		arsort($cat);
		return $cat;			
	}

	function getArticleReadTime($id){
		$article = $this->getId($id);
		if ($article){
			return $article->getDuration();
		} else {
			return 0;
		}
	}

	function getArticleReadTimes(){		
		$result=array();
		foreach ($this->articleEvents as $evt){
			$result[$evt->getId()] = $evt->getDuration();
		}
		arsort($result);
		return $result;
	}
	
	function getIds(){
		$ids = array();
		foreach ($this->articleEvents as $evt){
			$ids[] = $evt->getId();
		}
		return $ids;
	}

	function getCategoryCount(){			
		$cat = array();
		foreach ($this->articleEvents as $evt){
			if (!isset($cat[$evt->getCategory()])){
				$cat[$evt->getCategory()] = 0;
			}
			$cat[$evt->getCategory()]++;
		}
		arsort($cat);		
		return $cat;
	}

	function getCategory($category){
		$result = array();
		foreach ($this->articleEvents as $evt){
			if ($evt->getCategory() == $category){
				$result[] = $evt;
			}
		}
		return $result;
	}

	function getCategoryTimes(){
		$cat = array();
		foreach ($this->articleEvents as $evt){
			if (!isset($cat[$evt->getCategory()])){
				$cat[$evt->getCategory()] = 0;
			}
			$cat[$evt->getCategory()] += $evt->getDuration();
		}
		arsort($cat);
		return $cat;			
	}

	function getCategories(){
		return array_keys($this->getCategoryCount());
	}


}

?>