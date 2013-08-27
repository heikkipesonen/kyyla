<?php
/*
 * 
 * pile of articles
 */
class articleSet{
	private $max_keyphrases = 10;
	private $articles;
	private $db;

	function __construct($articles = null,$db = null){
		if (!$db){			
			$this->db = new db();
		} else {
			$this->db = $db;
		}

		if (is_array($articles) && count($articles)>0){
			foreach ($articles as $article){
				$this->add($article);
			}
		} else if ($articles != null){
			if (get_class($articles) == 'articleEventSet'){
				$articles = $articles->getEvents();
				foreach ($articles as $article){
					$this->add(new article($article) );
				}
			} else {
				if (get_class($article) == 'articleEvent'){
					$this->add( new article($article));
				} else {
					$this->add($article);
				}
			}
		}
	}

	function add($article){
		$this->articles[$article->getId()] = $article;
	}

	function getArticle($id){
		return $this->articles[$id];
	}
	
	function getReadArticles(){
		if (count($this->articles)>0){
			$list = array();
			foreach ($this->articles as $article){
				$list[] = $article->getId();
			}
			return $list;
		} else {
			return array();
		}
	}
/*
	function getWeightedArticlesByKeyphrase($limit = 10, $perCateogory = 10){
		$keyphrases = $this->getWeightedKeyphrases();
		$articleList = $this->db->getArticlesForKeyphrase($keyphrases,$limit);
		$articles = array();
				
		/*
		 * 
		 * 
		 * 			todo
		 * artikkelien painotus luettujen avainsanojen mukaan
		 * eli $keyphrases
		 * 	
		 * eli luetun avainsanan perusteella haetaan artikkeli
		 * 	-> lisätään luetun sanan painotus artikkelin painotukseen. 
		 * 	
		 * 
		 * 	artikkelin painotuksen säätö
		 */
	/*
		if ($articleList){
			foreach ($articleList as $id=>$article){
				$articles[$id] = getArticleWeight($article);//($article['value'] + $article['count']) * ($article['weight']);
			}
			arsort($articles);
			return scale($articles);
		} else {
			return false;
		}
		
	}
	*/
/*
 * 
 * 
 * avainsanotukseen painotus luetuille avainsanoille
 * eli jo artikkeleista löytyneille
 * 
 * --yleisesti luetuimmat kategoriat?
 * 
 */
/*
	function getWeightedCategoriesByKeyphrase(){
		
		$values = array();
		$result = array();
		$result_categories = array();
		$total = 0;

		$keyphrases = $this->getWeightedKeyphrases();
		if ($keyphrases && count($keyphrases)>0){
			foreach ($keyphrases as $phrase => $value){
				$categories = $this->db->getCategoriesForKeyphrase($phrase);
				foreach ($categories as $category){
					if (!isset($result_categories[$category['category']])){
						$result_categories[$category['category']]['weight'] =0;
						$result_categories[$category['category']]['count'] = 0;
					}
					$result_categories[$category['category']]['weight'] += $category['value'] + ($value*0.4);
					$result_categories[$category['category']]['count']++;
				}
			}
			foreach ($result_categories as $name => $category){
				$mid_result[$name] = $category['weight']*($category['count']/10);
				$total += $mid_result[$name];
			}
			foreach ($mid_result as $name => $weight){
				$result[$name] = $weight / $total;
			}
		}
		arsort($result);
		return $result;
	}

	function getWeightedKeyphrases(){
		$keyphrases = $this->getKeyphrases();
		$result = array();
		foreach ($keyphrases as $phrase){
			$result[$phrase['text']] = getKeyphraseWeight($phrase);//$phrase['value'] + ($phrase['count']) + $phrase['readtime'];
		}
		
		arsort($result);
		scale($result);
		return $result;
	}
*/
	function getKeyphrases($count = 10){
		$keyphrases = array();
		if ($this->articles){
			foreach ($this->articles as $article){
				foreach ($article->getKeyphrases() as $keyphrase => $value){
					if (!isset($keyphrases[$keyphrase])){
						$keyphrases[$keyphrase] = array();
						$keyphrases[$keyphrase]['count'] = 0;
						$keyphrases[$keyphrase]['value'] = 0;
						$keyphrases[$keyphrase]['readtime'] = 0;
					}
					$keyphrases[$keyphrase]['text']=$keyphrase;
					$keyphrases[$keyphrase]['count']++;
					$keyphrases[$keyphrase]['value']+=$value;
					$keyphrases[$keyphrase]['readtime']+=$article->getReadTime();
					$keyphrases[$keyphrase]['readdate']= $article->getReadDate();
				}
			}
			usort($keyphrases, 'sortKeyphrases');
			
			if ($count != null){
				$keyphrases = array_slice($keyphrases, 0,$count);	
			}			
		}
		return $keyphrases;
	}
}
?>