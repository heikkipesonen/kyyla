<?php
class recommender{
	private $user = null;
	private $db = null;
	
	function __construct($user,$db=null){
		if ($db){
			$this->db = $db;
		} else {
			$this->db = new db();
		}

		if (is_string($user)){
			$user = new user($user,$this->db);
		}
		
		$this->user = $user;		
	}
	
	function getPopular(){		
		return array('articles'=>$this->db->getMostReadArticles(),'categories'=>$this->db->getMostReadCategories());
	}
	
	function recommendArticleByKeyphrases($day = null,$limit = 5){		
		$phrases = $this->user->getReadKeyphrases();
		$list = array();
		foreach ($phrases as $phrase){
			$list[] = $phrase['text'];
		}
		$articles = $this->db->getArticlesForKeyphrases($list);
		
		$result = array();

		if ($articles){
			foreach ($articles as $article){
				$d = new rupu_timestamp($article['date']);
				$diff = log($d->getDiff());			
				
				$article['value'] = ($article['value'] / ($diff / 10))*log($article['count']);
				
				if ($article['value']>0){
					$result[$article['category']][$article['article_id']] = $article['value'];	
				}				
			}		
		}

		foreach ($result as $category => $articles){
			arsort($articles);
			$articles = array_slice($articles, 0,$limit,true);
			
			$result[$category] = $articles; 
		}
		
		return $result;
	}
	
	function getReadKeyphraseWeights($day = null,$limit = 20){
		$phrases = $this->user->getReadKeyphrases();
		$result = array();

		$list = array();
		foreach ($phrases as $phrase){
			$list[] = $phrase['text'];
		}
		
		$fs = $this->db->getKeyphraseFrequencies($list);			
		$total  = $this->db->getTotalKeyphrases();

		foreach ($phrases as $phrase){							
			$f = $fs[$phrase['text']];	
			
			$w = log(1+$phrase['count']) 
				* 
				log($total / (1+$f))
				+ ( $phrase['readtime']/100 )
				- ( log($phrase['readdate']->getDiff()) / 10 )
				;

			if ($w > 0){
				$result[$phrase['text']] =$w;	
			}
		}
		
		arsort($result);
		$result = array_slice($result,0,$limit,true);
		$result = scaleToMax($result);
		
		$list = array();
		if ($result){
			foreach ($result as $phrase => $w){			
				$q = $this->db->getCategoriesForKeyphrase($phrase,10);				
				if ($q){
					$cat = $q[0]['category'];
				} else {
					$cat = '';
				}
			
				$list[] = array('text'=>$phrase,'category'=>$cat,'value'=>$w);
			}
		}
		return $list;			
	}	
	
	function getReadCategoryWeights($day = null){
		$dailyWeights = $this->user->getReadDays($day)->getWeightsByDay();
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
	
	function getPopularityForCategories(){
		$popular = $this->db->getMostReadCategories();
		
		foreach ($popular as $category => $value){			
			$popular[$category] = log($value);
		}
		
		return $popular;
	}
	
	function getPopularityForArticles(){
		$articles = $this->db->getMostReadArticles(null,100);
		//print_r($articles);		
	}
	
	function getCombinedCategoryWeights($day = null){
		$cw = $this->getReadCategoryWeights($day);
		$aw = $this->getReadKeyphraseWeights($day);
		$popular = $this->getPopularityForCategories();
		
		$aws = array();
		foreach ($aw as $article){
			if (isset($aws[$article['category']])){
				$aws[$article['category']]['value'] += $article['value'];
				$aws[$article['category']]['count']++;
			} else {
				$aws[$article['category']]['value'] = $article['value'];
				$aws[$article['category']]['count'] = 1;				
			}
		}
		
		foreach ($aws as $cat => $data){
			$aws[$cat] = $data['value']/$data['count'];
		}
		
		$result = array();
				
		foreach ($cw as $category => $w){
			if (isset($aws[$category])){
				$w = $w * (1+($aws[$category]));	
			}		
			$result[$category] = $w*((1/$popular[$category])*4);
		}
	
		$result = scale($result);
		arsort($result);
		return $result;
	}
	
	function recommendArticles(){
		$cw = scale($this->getCombinedCategoryWeights());
		
		$aw = $this->recommendArticleByKeyphrases();

		$result = array();
		foreach ($aw as $category=>$articles){
			foreach ($articles as $article => $value){				
				if (isset($cw[$category])){
					$result[$category][$article] = $value * (1+($cw[$category]*3));	
				}
			}
		}
		return $result;
	}

	function recommend(){
		return array(
			'categories'=>$this->getReadCategoryWeights(),
			'articles'=>$this->recommendArticles()
		);
	}
}
?>