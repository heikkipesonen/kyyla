<?php
	class db{
		private $connection;
		private $time = 0;

		function __construct($user = 'mato',$password='sukkamato'){
			try{
				$this->connection = new PDO("mysql:host=localhost;dbname=rupu_usertracker",$user,$password);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
				$q = $this->connection->prepare('SET CHARACTER SET utf8');
				$q->execute();
			} catch (PDOException $e){
				echo $e->getMessage();			
				die( '{ok:false,message:"pdo init error"}' );
			}
		}

		function checkUser($user){
			$actions = $this->query('select * from user_action where user_id="'.$user.'"');			
			if (count($actions)>0){
				return true;
			} else {
				return false;
			}
		}
		
		function getMostReadCategories($limit = 100){			
			$result =  $this->query('
					select a.category, count(*) as count 
					from user_action as ua 
					join article as a on a.id = ua.data  
					where (action="open_article") and (ua.data IS NOT NULL) and (ua.data != "") 
					group by a.category
					order by count desc 
					limit '.$limit);

			if ($result){
				$res = array();
				foreach ($result as $c){
					$res[$c['category']] = $c['count'];
				}
				
				return $res;
			} else {
				return false;
			}
		}
		
		function getMostReadArticles($category = null, $limit=100){
			if ($category){
				$category = ' and (a.category = "'.$category.'")';
			} else {
				$category = '';
			}
			
			if ($limit != null && is_numeric($limit)){
				$limit = ' limit '.$limit;
			} else {
				$limit = '';
			}
			
			return $this->query('
					select a.category, ua.data as id, count(*) as count 
					from user_action as ua 
					join article as a on a.id = ua.data  
					where (action="open_article") and (ua.data IS NOT NULL) and (ua.data != "") '.$category.' 
					group by ua.data 
					order by count desc 
					'.$limit);		
		}
		
		function saveKeyphrases($article_id,$keyphrases){
			foreach ($keyphrases as $p){
				 $rows[] = '("'.$article_id.'","'.$p['text'].'",'.$p['count'].')';
			}
			$this->insert('insert into keyphrases (article_id,text,count) values '.implode(',', $rows));
		}		

		function getUsers(){
			$list=$this->query('select distinct user_id from user_action');
			$result = array();
			foreach ($list as $user){
				$result[] = $user['user_id'];
			}
			sort($result);
			return $result;
		}

		function insert($string){		
			$q = $this->connection->prepare($string);		
			$q->execute();
		}

		
		
		function getArticlesForKeyphrase($phrase,$days = 7, $limit = 10){
			$d = new rupu_timestamp();			
			$d->removeDay($days);
						
			$articles = $this->query('
					select a.pubdate as date, a.category, kp.article_id, kp.count, kp.text
					from keyphrases as kp
					join article as a on a.id = kp.article_id 
					where kp.text = "'.$phrase.'" and
					a.pubdate >= '.$d->getTimeStamp().'  					
					order by kp.count desc 
					');				
			
			return $articles;
		}
/*
 * 
 * 
 * 
 * 			tämä ei nyt ihan toimi
 */
		
		function getArticlesForKeyphrases($phrases,$days = 7, $limit = 10){			
			
			$d = new rupu_timestamp();			
			$d->removeDay($days);
			
			
			$articles = $this->query('
					select a.pubdate as date, a.category, kp.article_id, kp.count, kp.text
					from keyphrases as kp
					join article as a on a.id = kp.article_id 
					where kp.text in 
					("'.implode('","', $phrases ).'") and 
					a.pubdate >= '.$d->getTimeStamp().'
					order by kp.count desc'); 
					//'limit '.$limit);			
			
			if ($articles){
				$articleList = array();
				foreach ($articles as $article){			
					$id = $article['article_id'];
			
					if (!isset($articleList[$id])){
						$articleList[$id]['value']=0;
						$articleList[$id]['count']=0;
					}				
					$articleList[$id]['value']+=$article['count'];
					$articleList[$id]['count']++;
					$articleList[$id]['article_id'] = $id;
					$articleList[$id]['date'] = $article['date'];
					$articleList[$id]['category'] = $article['category'];
				}			

				return $articleList;
			} else {
				return false;
			}
		}

		function getCategoriesForKeyphrase($phrase, $limit = 1000){
			$q = $this->query('select 								
								a.category,
								k.count 
							from article as a 
							join keyphrases as k 
							on a.id = k.article_id 
							where k.text="'.$phrase.'" 
							order by k.count desc
							limit '.$limit);
		
			if ($q){
				$result = array();
				foreach ($q as $row){
					if (!isset($result[$row['category']])){
						$result[$row['category']] = array();
						$result[$row['category']]['count'] = 0;
						$result[$row['category']]['value'] = 0;
					}

					$result[$row['category']]['category'] = $row['category'];
					$result[$row['category']]['count']++;
					$result[$row['category']]['value'] += $row['count'];

				}
				usort($result,'sortKeyphrases');

				return $result;
			} else {
				return false;
			}
		}
		
		function getTime(){
			return $this->time;
		}

		function query($string){
			$t = microtime_float();
			try{	
				$q = $this->connection->prepare($string);
				$q->execute();
			} catch (PDOException $e){
				return false;
			}

			$rep = array();

			while ($result = $q->fetch(PDO::FETCH_ASSOC)){
				$rep[] = $result;
			}

			$td = microtime_float() - $t;
			$this->time += $td;
						
			return $rep;
		}

		function getKeyphrasesForArticles($article_id_array){
			$q = $this->query('select text,count as value from keyphrases where article_id in ("'.implode('","',$article_id_array).'") order by count desc');
			return $q;
		}
		
		function getKeyphraseFrequencies($phrases){
			$q = $this->query('select text,count(*) as count from keyphrases where text in ("'.implode('","',$phrases).'") group by text');			
						
			$result = array();
			
			foreach ($q as $phrase){
				$phrase['text'] = strtolower($phrase['text']);
				
				if (isset($result[$phrase['text']])){
					$result[$phrase['text']] += $phrase['count'];
				} else {
					$result[$phrase['text']] = $phrase['count'];
				}
			}
			return $result;
		}
		
		function getTotalKeyphrases(){
			$q = $this->query('select count(distinct text) as count from keyphrases');
			return $q[0]['count'];
		}

		function getTotalArticles(){
			$q = $this->query('select count(*) as count from articles');
			return $q[0]['count'];
		}
		
		
		function getKeyphraseFrequency($phrase){
			$q = $this->query('select count(*) as count from keyphrases where text="'.$phrase.'"');			
			if (isset($q[0])){
				return $q[0]['count'];
			} else {
				return 0;
			}
		}
		
		function getKeyphrases($article_id){
			$q = $this->query('select text,count from keyphrases where article_id="'.$article_id.'" order by count desc');
			

			if ($q){
				$result = array();
				foreach ($q as $row){
					$result[strtolower($row['text'])] = $row['count'];
				}

				return $result;
			} else {
				return false;
			}
		}

		function getArticle($id){
			$str = 'select * from article where id="'.$id.'"';
			$q = $this->query($str);
			if ($q){
				return $q[0];
			} else {
				return false;
			}

		}

		function getArticleCountForDay($day = false){
			if (!$day){
				$d = new DateTime();
				$day = new rupu_timestamp( $d->getTimeStamp() );
			}
			
			$end_time = $day->getTimeStamp();
			$day->removeDay(); // artikkelit julkaistaan edellisenä päivänä..... asglasffasdljksfadjlk
			$start_time = $day->getTimeStamp();						

			$str ='
				select category, count(*) as count from
					article where
					(pubdate >= '.$start_time.') and (pubdate <= '.$end_time.')
					group by category;
			';

			$q = $this->query($str);

			$res = array();
			
			foreach ($q as $row){
				$res[$row['category']] = $row['count'];
			}

			return $res;
		}

		function getUserAcitivityCount($username){
			$q = $this->query('select count(*) as count from user_action where user_id="'.$username.'" and action="open_article"');
			
			if ($q){
				return $q[0]['count'];	
			} else {
				return false;
			}
			
		}
		
		function getUserActivity($username){
			$articleEvents = array();

			$q = $this->query('select article.category,ua.session_id, ua.data, ua.client_time 
						from user_action as ua
						join article on ua.data = article.id
						where ua.user_id="'.$username.'"
								and (ua.action="open_article")');
			
			foreach ($q as $article){
				$closeEvent = $this->query('
						select client_time 
						from user_action 
						where data="'.$article['data'].'"
							and (user_id="'.$username.'") 
							and (session_id="'.$article['session_id'].'")
							and (action="close_article") 
							and (client_time >="'.$article['client_time'].'") ');		

				if (isset($closeEvent[0]['client_time'])){
					$articleEvents[] = new articleEvent($article['data'],$article['category'],$article['client_time'],$closeEvent[0]['client_time']);
				} else {
					$articleEvents[] = new articleEvent($article['data'],$article['category'],$article['client_time'],false);
				}
			}

			return $articleEvents;
		}

	}
?>