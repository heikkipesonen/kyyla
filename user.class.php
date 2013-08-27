<?php
/*
 * 
 * 		rupu user
 * 	eli kyylän kohde.
 */
	class user{
		private $name;
		
		private $read_articles; // luetut artikkelit
		private $read_categories;	// ..kategoriat
		private $db;
		private $articleEvents; // artikkelitapahtumat -> näistä revitään kaikki info
		

		function __construct($name,$db = null){
			if (!$db){
				$this->db = new db();
			} else {
				$this->db = $db;
			}
			
			if ($this->db->checkUser($name)){
				$this->name = $name;
				$this->articleEvents = new articleEventSet();
				$this->loadActivity();
			} else {
				die;
			}
		}
	
		function getName(){
			return $this->name;
		}

		function getActivity(){
			return $this->articleEvents;
		}

		function getActivityDates(){ // days when articles have been read
			return $this->articleEvents->getDates();
		}

		function getActivityForDay($day){ // one days activities -> read articles for that day
			return new articleEventSet( $this->articleEvents->getDay($day) );
		}

		function loadActivity(){ // loads events from database			
			$this->articleEvents->add( $this->db->getUserActivity($this->getName()) ,true );			
		}
		
		function getCategoryReadTimes($day = null){
			return $this->getActivityForDay($day)->getArticleReadTimes();
		}

		function getArticles($day = null){
			$all = new articleSet(null,$this->db);			
			if (!$day){
				$articleEvents = $this->articleEvents->getEvents();
			} else {
				$articleEvents = $this->articleEvents->getDay($day);
			}
			foreach ($articleEvents as $evt){
				$all->add(new article($evt,$this->db));
			}			
			return 	$all;
		}

		function getAllCategories(){
			return $this->articleEvents->getCategoryCount();
		}

		function getAllCategoriesTime(){
			return scale($this->articleEvents->getCategoryTimes());
		}

		/*
		 * 
		 * days events into one set
		 */
		function getReadDays($day = null){
			if (get_class($day)=='rupu_timestamp'){
				$day = $day->getDate();
			}
			
			$readPapers = new newspaperSet();
			$dates = $this->getActivityDates();
			foreach ($dates as $date){
				if ($day){
					if ($date == $day){
						$d = new newspaper($date,$this,$this->db);
						$readPapers->add($d);
					}		
				} else {
					$d = new newspaper($date,$this,$this->db);
					$readPapers->add($d);					
				}			
			}			
			return $readPapers;
		}		
		
		
		function getReadKeyphrases($day = null){
			$articles = $this->getArticles($day);
			return $articles->getKeyphrases(null);
		}
		
		function getReadArticles($day = null){
			return $this->getArticles($day)->getReadArticles();	
		}		
		
		
	
		
				
		/*
		function recommendArticlesByCategories($day = null, $limit = 50){
			$categories = $this->recommendCategoriesByRead(); // category weights
			$readTime = $this->articleEvents->getCategoryTimes(); // read time for categories			
			
			$articles = array();			
			// get most read articles for each category
			// as it might be itresting to read what others have read.
			foreach ($categories as $category=>$value){
				$articles[$category] = $this->db->getMostReadArticles($category,$limit); 				
			}
			
			$result = array();							
			foreach ($articles as $category => $articles){
				foreach ($articles as $article){			
					$ccat= $article['category'];
					$result[$article['id']] = $readTime[$ccat] + ($categories[$ccat]*17) + $article['count'];	
				}							
			}			
			arsort($result);
			return scale($result);
		}
		*/

		/*
		function recommendCategories($day = null){
			$c1 = $this->recommendCategoriesByRead($day);
			//$c2 = $this->recommendCategoriesByKeyphrase($day);
			$result = array();
		
			foreach ($c1 as $name=>$value){
				if (isset($c2[$name])){
					$result[$name]=$value + $c2[$name];
				} else {
					$result[$name]=$value;
				}
			}
			arsort($result);
			
			return scale($result);
		}
		
		
		function recommendCategoriesByRead($day = null){
			$dailyWeights = $this->getReadDays($day)->getWeightsByDay();
			$categoryWeights = array();
			foreach ($dailyWeights as $dayWeight){				
				foreach ($dayWeight as $category => $weight){
					if (!isset($categoryWeights[$category])){
						$categoryWeights[$category] = 0;
					}
					$categoryWeights[$category] += $weight;					
				}
			}
			arsort($categoryWeights);
			return scale($categoryWeights);
		}
		*/
		

		
		/*
		function recommendArticlesByKeyphrase($day = null,$limit = 50){
			$articles = $this->getArticles($day);
			return scale($articles->getWeightedArticlesByKeyphrase($limit));
		}
		
		
		function recommendCategoriesByKeyphrase($day= null){
			$articles = $this->getArticles($day);
			return $articles->getWeightedCategoriesByKeyphrase();
		}
		

		function recommendByDate($day = null){
			$days = $this->getActivityDates();
			$result = array();
			foreach ($days as $day){
				$result[$day]['categories'] = $this->recommendCategories($day);
				$result[$day]['categoriesbykeyphrase'] = $this->recommendCategoriesByKeyphrase($day);
				$result[$day]['articlesbykeyphrase'] = $this->recommendArticlesByKeyphrase($day);
			}
			
			return $result;
		}
		*/
	
		/*
		 * 
		 * 
		 * suosittelu
		 */
		
		
		/*
		function recommend($day = null,$limit = 10){				
			// category recommendation
			$categoriesByRead = scale($this->recommendCategoriesByRead());
			//$categoriesByKeyphrase = scale($this->recommendCategoriesByKeyphrase($day));			

			//$categoriesByKeyphrase = multiply($categoriesByKeyphrase, 0.8); // some weight reduction
			//$categoriesByRead = multiply($categoriesByRead,1.4);			
			// get recommendation
			//$cat_recommendation = filterSmallValues(getArraySum(array($categoriesByKeyphrase,$categoriesByRead)));
		
			$cat_recommendation = $categoriesByRead;
			
			
			
			// article recommendation
			//$articlesByKeyphrase = $this->recommendArticlesByKeyphrase($day,$limit*10);
			$articlesByCategory = $this->recommendArticlesByCategories($day);						
			//$articlesByCategory = array_slice($articlesByCategory,0, count($articlesByKeyphrase));			
			
			//$articlesByKeyphrase = multiply(scale($articlesByKeyphrase),0.8);
			$articlesByCategory =multiply(scale($articlesByCategory),1.2);												
			//$art_recommendation = getArraySum(array($articlesByCategory,$articlesByKeyphrase));
			$art_recommendation = $articlesByCategory;
			
			arsort($cat_recommendation);
			arsort($art_recommendation);
			
			$art_recommendation = array_slice( $art_recommendation,0,$limit );
			
			
			$result['categories'] = scale($cat_recommendation);
			$result['articles'] = scale($art_recommendation);			
			return $result;
		}
				*/		
		

		
	}
?>