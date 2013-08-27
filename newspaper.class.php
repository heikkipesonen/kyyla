<?php
	/*
	 * 
	 * 	represents days newspaper which the user has read
	 * 
	 */

class newspaper{
	private $db;	
	private $numberOfArticles;
	private $articleEvents;
	private $categories;

	function __construct($date,$user,$db = null){		
		if ($db== null){
			$this->db = new db();
		} else {
			$this->db = $db;
		}

		$this->user = $user;
		$this->date = new rupu_timestamp($date);

		$this->numberOfArticles = $this->db->getArticleCountForDay(new rupu_timestamp($date));
		$this->articleEvents = $this->user->getActivityForDay(new rupu_timestamp($date));

		$this->calculateWeights();
	}
	
	function calculateWeights(){
		/*
		 * 
		 * 	category weighting
		 * 
		 */
		$categories = $this->getCategories();
		foreach ($categories as $category){				
			if ($this->getCategoryReadTime($category) > 0){				
				
				// print_r:ble array for dev...
				$cw = array(
						'readtimeratio'=>$this->getCategoryReadTimeRatio($category)*10,
						'readtime'=>log($this->getCategoryReadTime($category))*1.4,
						'readarticles'=>log($this->getCategoryReadArticles($category)),
						'readarticleratio'=>log(1+$this->getCategoryReadArticleRatio($category)),
						'articlesincategory'=>$this->getCategoryReadArticles($category)/$this->getNumberOfArticlesInCategory($category),						
						'readarticlescategoryratio'=>log(1+$this->getCategoryReadArticles($category) / $this->getNumberOfArticlesInCategory($category))
				);

	/*			
				print_r(scale($cw));
				print_r(array_sum( array_values($cw)));
		*/		
				
				$this->categories[$category] = array_sum( array_values($cw));
			}

		}
	}	

	function getDateString(){
		return $this->date->getDate();
	}

	function getDate(){
		return $this->date;
	}

	function getScaledCategoryWeights(){		
		return scale($this->getCategoryWeights());
	}

	function getCategoryWeights(){
		$result = array();
		if ($this->categories){		
			foreach ($this->categories as $category => $weight){
				$result[$category] = $weight;
			}
		}
		arsort($result);
		return $result;
	}


	function getReadTimeRatio(){
		$categoryTimes = $this->articleEvents->getCategoryTimes();
		$categoryTimes = scale($categoryTimes);
		return $categoryTimes;
	}
	
	function getCategoryReadTimeRatio($category){
		$c = $this->getReadTimeRatio();		
		if (isset($c[$category])){
			return $c[$category];	
		} else {
			return 0;
		}
	}
	
	function getCategoryReadArticles($category){
		$c = $this->getReadArticles();
		if (isset($c[$category])){
			return $c[$category];	
		} else {
			return 0;
		}	
	}
	
	function getReadArticleRatio(){
		$cats = $this->articleEvents->getCategoryCount(); // read articles in category
		return scale($cats);
	}
	
	function getCategoryReadArticleRatio($category){
		$c = $this->getReadArticleRatio();
		if (isset($c[$category])){
			return $c[$category];	
		} else {
			return 0;
		}	
	}
	
	function getCategoryReadTime($category){
		$c = $this->getReadTime();
		if (isset($c[$category])){
			return $c[$category];	
		} else {
			return 0;
		}	
	}

	function getCategories(){
		//return $this->categories;
		return $this->articleEvents->getCategories();		
	}	
	
	function getNumberOfArticlesInCategory($category){		
		$c = $this->numberOfArticles;		
		if (isset($c[$category])){
			return $c[$category];	
		} else {
			return 1;
		}		
	}
	
	function getTotalArticlesRead(){		
		return array_sum( array_values($this->getReadArticles()) );
	}

	function getTotalNumberOfArticles(){
		return array_sum( array_values ($this->numberOfArticles) );
	}

	function getTotalReadTime(){
		return $this->articleEvents->getTotalReadTime();
	}

	function getReadTime(){
		return $this->articleEvents->getReadTime();
	}

	function getReadArticles(){
		return $this->articleEvents->getCategoryCount();
	}
}
?>

