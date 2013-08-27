<?php
	class article{		
		private $text;
		private $author;
		private $pubdate;
		private $title;
		private $articleEvent;

		private $keyphrases;
		
		function __construct($articleEvent,$db = null){
			$this->articleEvent = $articleEvent;
			
			if (!$db){
				$this->db = new db();				
			} else {
				$this->db = $db;
			}

			$this->getKeyphrases();
		}		

		function getId(){
			return $this->articleEvent->getId();
		}

		function loadKeyphrases(){
			if (!$this->text){
				$this->getContent();
			}
			$keyphrases = getKeyphrases($this->text);
			if ($keyphrases){
				$this->db->saveKeyphrases($this->id,$keyphrases);

				$result = array();
				foreach ($keyphrases as $row){
					$result[strtolower($row['text'])] = $row['count'];
				}
				$this->keyphrases = $result;
			}
		}
		
		function getReadTime(){
			return $this->articleEvent->getDuration();
		}
		
		function getReadDate(){
			return $this->articleEvent->getStartTime();
		}

		function getKeyphrases(){
			if (!$this->keyphrases){
				$phrases = $this->db->getKeyphrases($this->getId());
				if (!$phrases){
					$this->loadKeyphrases();
				} else {
					$this->keyphrases = $phrases;
				}
			}
			return $this->keyphrases;
		}

		function setContent($articleContent){
			$this->title = $articleContent['title'];
			$this->text = $articleContent['text'];
			$this->author = $articleContent['author'];
			$this->category = $articleContent['category'];
			$this->pubdate = new rupu_timestamp( $articleContent['pubdate'] );			
		}

		function getContent(){
			$d = $this->db->getArticle($this->id);
			if ($d){
				$this->setContent($d); // lol wut
			}
		}



	}
?>