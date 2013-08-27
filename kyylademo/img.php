<?php
	error_reporting(0);
	require_once('SimpleImage.class.php');

	if (isset($_GET['img'] )){
		$img = new SimpleImage();
		$img->loadFromUrl('../../puru/images/'.$_GET['img']);
		
		if (isset($_GET['width'])){
			$img->resizeToWidth($_GET['width']);
		} else if (isset($_GET['crop'])){			
			$img->crop($_GET['crop'],$_GET['crop']);
		}

		if (isset($_GET['filter'])){			
			$img->filter($_GET['filter']);			
		}

		$img->output();
	}


	if (isset($_GET['list'])){
		echo json_encode(getList($_GET['list']));
	}

	function isImage($name){
	  $is = false;
	  if ($name != '.' && $name != '..'){
	    $ext = substr($name,-4);
	    
	    if ($ext == '.png' || $ext == '.jpg' || $ext == '.gif' || $ext == 'jpeg'){
	      $is = true;
	    }
	  }

	  return $is;
	}

	function getList($dir){	    
	    $handle = opendir($dir);
	    $result = Array();

	    while (($file = readdir($handle)) != false){
	      if (isImage($file)){
	          $result[] = Array($file);
	       }
	    }
	    return $result;
	}		

	if (isset($_GET['images'])){
		echo json_encode( getList() );
	}


	if (isset($_POST['images']) && is_array($_POST['images'])){
		$result = array();
		/*
		if (!file_exists('img')){
			mkdir('img',0777);
		}
		*/

		foreach ($_POST['images'] as $image) {


			if ($image['type'] == 'jpg' || $image['type'] == 'jpeg' || $image['type'] == 'png'){
				
				$img = new SimpleImage();
				$success = $img->loadBase64($image['data']);
								

				if ($success){
					if ($image['filter']){
						$img->filter($image['filter']);
					}

					$save = $img->save('images/'.$image['name']);
					$img->resizeToHeight(512);					
					
					$save = $img->save('images/small/'.$image['name']);
					$img->crop(256,256);
					$save = $img->save('images/thumbnail/'.$image['name']);					
					$result[] = array('status' =>'ok','name'=>$image['name']);
				} else {
					$result[] = array('status' =>'error','name'=>$image['name']);
				}
			} else {
					$result[] = array('status' =>'error','name'=>$image['name']);
			}
		}

		echo json_encode($result);
	}


?>
