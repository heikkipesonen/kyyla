
<?php
	require_once('kyyla.php');
	$t = microtime_float();
/*
 * 
 * 					testijooseppi 0.2
 */


	function updateUserData($user){
		$udb = new db();
		$u = new user($user);
		$r = new recommender($u);
		
		$result =array('readarticles'=>$u->getCategoryReadTimes(),//getActivity()->getArticleReadTimes(),
				'categoryreads'=>$u->getAllCategories(),
				'readtime'=>$u->getAllCategoriesTime(),
				'totaltime'=>$u->getActivity()->getTotalReadTime(),
				'keyphrasearticles'=>$r->recommendArticleByKeyphrases(),
				'articles'=>$r->recommendArticles(),
				'keyphrases'=>$r->getReadKeyphraseWeights(),
				'categoriesbyread'=>$r->getReadCategoryWeights(),
				'categories'=>$r->getCombinedCategoryWeights()
		);
		$d = new rupu_timestamp();
		$udb->insert('insert into temp (user,time,data) values ("'.$user.'",'.$d->getTimeStamp().',"'.base64_encode(json_encode($result)).'")');
		return $result;		
	}
	

		$db = new db();				
		$users = $db->getUsers();
		
		foreach ($users as $user){
			updateUserData($user);
		}

		echo '--'.$db->getTime().'-';
		echo microtime_float()-$t;
?>
