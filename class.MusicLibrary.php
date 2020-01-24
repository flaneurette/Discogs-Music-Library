<?php

class MusicLibrary {

	CONST MUSICLIBRARY  = "./Library.json";
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
		return json_decode(file_get_contents(self::MUSICLIBRARY), true, self::DEPTH, JSON_BIGINT_AS_STRING);
	}
	
	public function addmusic() 
	{
		$newmusic = 
			array(
			  "listing_id" => "{$this->listing_id}",
			  "artist" => "{$this->artist}",
			  "title" => "{$this->title}",
			  "label" => "{$this->label}",
			  "catno" => "{$this->catno}",
			  "format" => "{$this->format}",
			  "release_id" => "{$this->release_id}",
			  "status" => "{$this->status}",
			  "price" => "{$this->price}",
			  "listed" => "{$this->listed}",
			  "media_condition" => "{$this->media_condition}",
			  "sleeve_condition" => "{$this->sleeve_condition}",
			  "accept_offer" => "{$this->accept_offer}",
			  "weight" => "{$this->weight}",
			  "format_quantity" => "{$this->format_quantity}",
			  "flat_shipping" => "{$this->flat_shipping}"
		);
		
		$lijst = $this->decode();
		$i = count($lijst);
		$lijst = array($newmusic);

		$this->storemusic($lijst);
	}

	public function editmusic($id) 
	{
		// todo! 
	}

	public function checkForm() 
	{
	
      isset($_POST['listing_id']) ? 		$this->listing_id = $this->cleanInput($_POST['listing_id']) : $listing_id = false;
      isset($_POST['artist']) ? 		$this->artist = $this->cleanInput($_POST['artist']) : $artist = false;
      isset($_POST['title']) ? 		$this->title = $this->cleanInput($_POST['title']) : $title = false;
      isset($_POST['label']) ? 		$this->label = $this->cleanInput($_POST['label']) : $label = false;
      isset($_POST['catno']) ? 		$this->catno = $this->cleanInput($_POST['catno']) : $catno = false;
      isset($_POST['format']) ? 		$this->format = $this->cleanInput($_POST['format']) : $format = false;
      isset($_POST['release_id']) ? 		$this->release_id = $this->cleanInput($_POST['release_id']) : $release_id = false;
      isset($_POST['status']) ? 		$this->status = $this->cleanInput($_POST['status']) : $status = false;
      isset($_POST['price']) ? 		$this->price = $this->cleanInput($_POST['price']) : $price = false;
      isset($_POST['listed']) ? 		$this->listed = $this->cleanInput($_POST['listed']) : $listed= false;
      isset($_POST['media_condition']) ? 		$this->media_condition = $this->cleanInput($_POST['media_condition']) : $media_condition = false;
      isset($_POST['sleeve_condition']) ? 		$this->sleeve_condition = $this->cleanInput($_POST['sleeve_condition']) : $sleeve_condition = false;
      isset($_POST['accept_offer']) ? 		$this->accept_offer = $this->cleanInput($_POST['accept_offer']) : $accept_offer = false;
      isset($_POST['weight']) ? 		$this->weight = $this->cleanInput($_POST['weight']) : $weight = false;
      isset($_POST['format_quantity']) ? 		$this->format_quantity = $this->cleanInput($_POST['format_quantity']) : $format_quantity = false;
      isset($_POST['flat_shipping']) ? 		$this->flat_shipping = $this->cleanInput($_POST['flat_shipping']) : $flat_shipping = false;
	  

		$_SESSION['messages'] = array();

		if($this->title != false) {
			if(strlen($this->title) > 60 ) {
				$this->message('Title may not be longer than 60 characters.');
				return false;
			}
		}  else {
				$this->message('Title may not be empty.');
				return false;
		}

		if($this->weight != false) {
			if(!is_int((int)$this->weight) || preg_match("/[a-zA-Z]/i",$this->weight)) { 
				$this->message('Weight may not contain characters.');
				return false;
			}
		}  else {
				$this->message('Weight must not be empty.');
				return false;
		}

		if($this->artist != false) {
			if(strlen($this->artist) > 60 ) {
				$this->message('Artist may not be longer than 60 characters.');
				return false;
			}
		}  else {
				$this->message('Description may not be empty.');
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
		$file 	= self::MUSICLIBRARY;
		$copy 	= self::MUSICLIBRARY.'.bak';
		@copy($file, $copy);
		// convert encoding
		$json = mb_convert_encoding($this->encode($music), self::FILE_ENC, self::FILE_OS);
		// write file.
		file_put_contents(self::MUSICLIBRARY,$json, LOCK_EX);
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
