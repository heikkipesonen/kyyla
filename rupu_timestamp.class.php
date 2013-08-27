<?php
	date_default_timezone_set('Europe/Helsinki');

	class rupu_timestamp{
		private $timestamp;

		function __construct($timestamp = null){
			if (!is_numeric($timestamp) && is_string($timestamp)){
				$timestamp = strtotime($timestamp);
			} else if ($timestamp >  1000000000000 ){
				$timestamp = $timestamp / 1000;
			} else if ($timestamp == null){				
				$timestamp = time();				
			}
						
			$this->timestamp = $timestamp;			
		}			
		
		function duration($time1,$time2){
			return new rupu_timestamp($time2->getTimeStamp() -> $time1->getTimeStamp());
		}
		
		function getDiff($date = null){
			if (is_string($date)){
				$d = new rupu_timestamp($date);
				$date = $d->getTimeStamp();
			} else if ($date == null){
				$date = time();
			}
			
			return $date - $this->getTimeStamp();
		}
		
		function addDay($count = 1){
			$d = (24 * 60 * 60) * $count;
			
			$this->timestamp += $d;
		}
		
		function add($time){
			$this->timestamp+=$time;
		}

		function removeDay($count = 1){
			$this->addDay(0 - $count);
		}

		function getDate(){
			return date('d.m.Y',$this->timestamp);
		}

		function getTime(){
			return date('H.i.s',$this->timestamp);	
		}

		function getDateTime(){
			return $this->getDate().' : '.$this->getTime();
		}

		function getTimeStamp(){
			return $this->timestamp;
		}

		function __toString(){
			return ''.$this->timestamp;
		}

		function getRupuTimeStamp(){
			return $this->timestamp*1000;
		}
	}
?>