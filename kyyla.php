<?php
	ini_set('html_errors', false);
	function __autoload($class){
		require_once($class.'.class.php');
	}
	require_once('functions.php');
	

	/*
	 * 
	 * 
	 * 
	 * äärimmäisen turvallinen get-api kyylälle
	 * 
	 * 
	 * pääasiallinen toiminnallisuus user.php:ssäö,zsfdkölsdf
	 */
	
	
	if (isset($_GET)){
		$db = new db();		
	}
	
	
	if (isset($_GET['article'])){
		reply($db->getArticle($_GET['article']));
	}		
	
	if (isset($_GET['users'])){		
		reply($db->getUsers());
	}
	
	if (isset($_GET['user'])){
	 	if ($_GET['type']=='activity'){				
	 		$user = new user($_GET['user']);
			reply($db->getUserAcitivityCount($user->getName()));
		} else {
			$r = $db->query('select * from temp where user="'.$_GET['user'].'" order by time desc limit 1');
			if ($r){
				$r = $r[0];			
				$data = json_decode(base64_decode($r['data']));
				
				reply($data);
			}
			
		}
		/*
		if ($db->checkUser($_GET['user'])){
			$user = new user($_GET['user']);
			$r = new recommender($user);
			
			$day = null;
			if (isset($_GET['date'])){
				$day = $_GET['date'];
			}
		
			if (!isset($_GET['type'])){
				reply($r->recommend($day));
			} else if ($_GET['type']=='statistics'){
				
				reply(array(
					'readarticles'=>$user->getCategoryReadTimes(),//getActivity()->getArticleReadTimes(),
					'categoryreads'=>$user->getAllCategories(),
					'readtime'=>$user->getAllCategoriesTime(),
					'totaltime'=>$user->getActivity()->getTotalReadTime(),
					'keyphrasearticles'=>$r->recommendArticleByKeyphrases(),
					'articles'=>$r->recommendArticles(),
					'keyphrases'=>$r->getReadKeyphraseWeights(),
					'categoriesbyread'=>$r->getReadCategoryWeights(),
					'categories'=>$r->getCombinedCategoryWeights()
						
				));
			} else if ($_GET['type']=='categories'){
				reply($r->recommendCategories($day));
			} else if ($_GET['type']=='articlesbykeyphrase'){
				reply($r->recommendArticleByKeyphrases());
				//reply($user->recommendCategoriesByKeyphrase($day));
			} else if ($_GET['type']=='categoriesbyread'){
				//reply($user->recommendCategoriesByRead($day));
			} else if ($_GET['type']=='combinedcategories'){
				reply($r->getCombinedCategoryWeights());
			} else if ($_GET['type']=='activity'){				
				reply($db->getUserAcitivityCount($user->getName()));
			} else if ($_GET['type']=='keyphrasecloud'){
				reply($r->getReadKeyphraseWeights());	
			}
		} else {
			reply(false);
		}
		*/
	}
	
	function reply($response){
		if ($response != 'e' && count($response)>0 && $response){
			echo json_encode($response);	
		} else {
			echo json_encode(array('ok'=>false));
		}		
	}
		

?>