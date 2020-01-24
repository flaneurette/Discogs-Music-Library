<?php

class MusicLibrary {

	CONST musicCASE  = "./Library.json";
	CONST DEPTH	= 1024;
	CONST MAXWEIGHT = 10000;
	CONST PWD 	= "Password to encrypt"; // optional.
	CONST FILE_ENC  = "UTF-8";
	CONST FILE_OS   = "WINDOWS-1252";
	
	public function __construct() {
		$incomplete = false;
	}
	/**
	* Sanitizes user-input
	* @param string
	* @return string
	*/
	public function cleanInput($string) 
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}
	/**
	* Encodes JSON object
	* @param music
	* @return void
	*/
	public function encode($music) 
	{
		return json_encode($music, JSON_PRETTY_PRINT);
	}
	/**
	* Loads and decodes JSON object
	* @return mixed object/array
	*/
	public function decode() 
	{
		return json_decode(file_get_contents(self::musicCASE), true, self::DEPTH, JSON_BIGINT_AS_STRING);
	}
	
	public function addmusic() 
	{
		$newmusic = 
			array(
			"title" => "{$this->titlemusic}", 
			"isbn" => "{$this->isbnmusic}",
			"weight" => "{$this->weightmusic}",
			"description" => "{$this->intromusic}"
		);
		$lijst = $this->decode();
		$i = count($lijst);
		if($i >=1) {
			usort($lijst, $this->sortISBN('isbn'));
			array_push($lijst,$newmusic);
			} else {
			$lijst = array($newmusic);
		}
		$this->storemusic($lijst);
	}

	public function editmusic($id) 
	{
		// todo! 
	}

	public function checkForm() 
	{
		isset($_POST['title']) ? 		$this->titlemusic = $this->cleanInput($_POST['title']) : $titlemusic = false;
		isset($_POST['isbn']) ? 		$this->isbnmusic = $this->cleanInput($_POST['isbn']) : $isbnmusic = false;
		isset($_POST['weight']) ? 		$this->weightmusic = $this->cleanInput($_POST['weight']) : $weightmusic = false;
		isset($_POST['description']) ?  $this->intromusic = $this->cleanInput($_POST['description']) : $intromusic = false;

		$_SESSION['messages'] = array();

		if($this->titlemusic != false) {
			if(strlen($this->titlemusic) > 60 ) {
				$this->message('Title may not be longer than 60 characters.');
				return false;
			}
		}  else {
				$this->message('Title may not be empty.');
				return false;
		}

		if($this->isbnmusic != false) {
			if(!preg_match("/[a-zA-Z]/i",$this->isbnmusic)) {  
				if(strlen($this->isbnmusic) > 13 || strlen($this->isbnmusic) < 13) { 
					$this->message('ISBN number is wrong. (13 digits.)');
					return false;
				}
			} else {
					$this->message('ISBN number may only contain numbers!');
					return false;
			}
		} 

		if($this->weightmusic != false) {
			if(!is_int((int)$this->weightmusic) || preg_match("/[a-zA-Z]/i",$this->weightmusic)) { 
				$this->message('Weight may not contain characters.');
				return false;
			}
		}  else {
				$this->message('Weight must not be empty.');
				return false;
		}

		if($this->intromusic != false) {
			if(strlen($this->intromusic) > 60 ) {
				$this->message('Description may not be longer than 60 characters.');
				return false;
			}
		}  else {
				$this->message('Description magy not be empty.');
				return false;
		}

	}

	public function message($value) 
	{
		if(isset($_SESSION['messages'])) { 
			array_push($_SESSION['messages'],$value);  
			} else { 
			$_SESSION['messages'] = array(); 
		} 	
	}

	public function showmessage() 
	{ 
		if(isset($_SESSION['messages'])) { 
			echo "<pre>"; 
			echo "<strong>Message:</strong>\r\n"; 
			foreach($_SESSION['messages'] as $message) { 
				echo $message . "\r\n" ; 
			} echo "</pre>"; 
		} 
		$_SESSION['messages'] = array();
	} 

	/**
	* Store music into musicLibrary
	* @param array $music
	* @return boolean, true for success, false for failure.
	*/
	public function storemusic($music) 
	{
		// make a backup before doing anything.
		$file 	= self::musicCASE;
		$copy 	= self::musicCASE.'.bak';
		@copy($file, $copy);
		// convert encoding
		$json = mb_convert_encoding($this->encode($music), self::FILE_ENC, self::FILE_OS);
		// write file.
		file_put_contents(self::musicCASE,$json, LOCK_EX);
	}
	
	public function deletemusic($music) 
	{
		$lijst = $this->decode();
		if($lijst !== null) {
			$libraylist = usort($lijst, $this->sortISBN('isbn'));
			$musics = array();
			foreach($lijst as $c) {	
				echo $music."<br>";
				if($c['isbn'] != $music) {
					array_push($musics,$c);
				}
			}
		}
		$this->storemusic($musics);
	}

	public function sortISBN($key) {
		return function ($a, $b) use ($key) {
			return strnatcmp($a[$key], $b[$key]);
		};
	}
	
	// We don't use this, but you could call it to encrypt the JSON data.
	public function encrypt($plaintext) {
		
		$key = self::PWD; // Password is set above at the Constants
		$ivlen = openssl_cipher_iv_length($cipher="AES-256-CTR");
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		$ciphertext = base64_encode($iv.$hmac.$ciphertext_raw );
	
		return bin2hex($ciphertext);
	
	}
	
	// We don't use this, but you could call it to decrypt the JSON data.
	public function decrypt($ciphertext) {
		
		$key = self::PWD; // Password is set above at the Constants
		$ciphertext = hex2bin($ciphertext);
		$c = base64_decode($ciphertext);
		$ivlen = openssl_cipher_iv_length($cipher="AES-256-CTR");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len=32);
		$ciphertext_raw = substr($c, $ivlen+$sha2len);
		$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		
		if (hash_equals($hmac, $calcmac)) { //PHP 5.6+ timing attack safe comparison
			return $original_plaintext;
		}
	}
}

?>
