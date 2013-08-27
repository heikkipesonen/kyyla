<?php
class articleEvent{
	protected $start_time;
	protected $end_time;
	protected $category;
	protected $date;
	protected $id;

	function __construct($id,$category=null,$start_time=null,$end_time=false){
		$this->id = $id;
		$this->category = $category;
		$this->start_time = new rupu_timestamp($start_time);
		
		if ($end_time){
			$this->end_time = new rupu_timestamp($end_time);
		} else {
			$this->end_time = false;
		}
	}	
	
	function getId(){
		return $this->getArticleId();
	}

	function getArticleId(){
		return $this->id;
	}

	function getDuration(){	
		if ($this->end_time){
			if ($this->end_time->getTimeStamp() < $this->start_time->getTimeStamp()){
				return 0;
			} else {
				return $this->end_time->getTimeStamp() - $this->start_time->getTimeStamp();
			}
		} else {
			return 0;
		}
	}

	function addTime($time){
		if ($this->end_time){
			$this->end_time->add($time);
		} else {
			$this->end_time = new rupu_timestamp($this->start_time->getTimeStamp());
			$this->end_time->add($time);
		}
	}

	function getWeight(){
		if ($this->getDuration > 0){
			$time = $this->getDuration() / 60;
		} else {
			return $time=0;
		}

		return $time;
	}

	function getEndTimeStamp(){
		if ($this->end_time){
			return $this->end_time->getTimeStamp();
		} else {
			return false;
		}
	}

	function getStartTimeStamp(){
		return $this->start_time->getTimeStamp();
	}


	function getDate(){
		return $this->start_time->getDate();
	}

	function getReadTime(){
		return $this->start_time->getTime();
	}

	function getStartTime(){
		return new rupu_timestamp( $this->start_time->getTimeStamp() );
	}

	function getEndTime(){
		return  new rupu_timestamp( $this->end_time->getTimeStamp() );
	}

	function getCategory(){
		return $this->category;
	}
}

?>