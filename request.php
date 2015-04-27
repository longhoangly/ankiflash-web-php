<?php
error_reporting(E_ERROR);

session_start();
$sid = substr(session_id(), -8);

// get the q parameter from URL
$q = $_REQUEST["q"];

if($q=="again") {

	recursiveRemoveDirectory("$sid");
	unlink("archive_$sid.zip");
	unlink("writedata_$sid.log");
	sleep(1);
	mkdir("$sid", 0755, true);
	sleep(1);
	mkdir("$sid/images", 0755, true);
	mkdir("$sid/sounds", 0755, true);
	mkdir("$sid/htmls", 0755, true);
	
} else if($q=="close") {

	recursiveRemoveDirectory("$sid");
	unlink("archive_$sid.zip");
	unlink("writedata_$sid.log");

} else if($q=="download") {

	$directories = array("icons", "oxlayout", "cardform");
	$archivName = "archive_$sid.zip";
	
	$zipObj = new ZipArchive;
	$zipObj->open($archivName, ZipArchive::CREATE);
	zpr($directories, $archivName, "archive", $zipObj);
	zpr3(array($sid), $archivName, "archive", $zipObj, $sid);
	$zipObj->close();
	
	sleep(1);
	$ziplink = '<a href="http://flashcardsgenerator.com/' . "archive_$sid.zip" . '" style="font-size: 12px; color: white">Click here to download archived flashcards</a>';
	echo $ziplink;
	
} else {

	$servername = "localhost";
	$username = "root";
	$password = "nec";
	$dbname = "anki";
	$table = "tblFlashcards";
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	
	$words = explode( '|', $q );
	foreach($words as $w) {

		$sql = "SELECT flsWord,flsSound_uk,flsSound_us,flsThumb,flsImg,flsAnkiDeck FROM $table WHERE flsWord=\"$w\";";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$word = $sound_uk = $sound_us = $thumb = $img = $ankiDeck = "";
			
			$query = "UPDATE $table SET flsLastQueryTime=NOW() WHERE flsWord=\"$w\";";
			$update = $conn->query($query);
			
			while($row = $result->fetch_assoc()){
				$word = $row["flsWord"];
				$sound_uk = $row["flsSound_uk"];
				$sound_us = $row["flsSound_us"];
				$thumb = $row["flsThumb"];
				$img = $row["flsImg"];
				$ankiDeck = $row["flsAnkiDeck"];
			}			

			file_put_contents("$sid/ankiDeck.csv", $ankiDeck, FILE_APPEND | LOCK_EX);
			echo $ankiDeck;

			file_put_contents("./writedata_$sid.log", "This word [$w] is already added to database.\n$ankiDeck.\n", FILE_APPEND | LOCK_EX);

			$mp3Source = preg_replace("/\]/i", "", $sound_uk);
			$mp3SourceSplited = explode('/',$mp3Source);
			$mp3SourceFileName = end($mp3SourceSplited);
			file_put_contents("$sid/sounds/$mp3SourceFileName", "./soundCollection/$mp3SourceFileName");
	
			$mp3Source = preg_replace("/\]/i", "", $sound_us);
			$mp3SourceSplited = explode('/',$mp3Source);
			$mp3SourceFileName = end($mp3SourceSplited);
			file_put_contents("$sid/sounds/$mp3SourceFileName", "./soundCollection/$mp3SourceFileName");
	
			if(preg_match("/src=/i", $thumb)) {
				$thumbSource = preg_replace("/\"\/\>/i", "", $thumb);
				$thumbSourceSplited = explode('/',$thumbSource);
				$thumbSourceFileName = end($thumbSourceSplited);
				file_put_contents("$sid/images/$thumbSourceFileName", "./imageCollection/$thumbSourceFileName");
			}
	
			if(preg_match("/src=/i", $img)) {
				$imgSource = preg_replace("/\"\/\>/i", "", $img);
				$imgSourceSplited = explode('/',$imgSource);
				$imgSourceFileName = end($imgSourceSplited);
				file_put_contents("$sid/images/$imgSourceFileName", "./imageCollection/$imgSourceFileName");
			}
	
			if(preg_match("/<iframe marginwidth=\"20\" marginheight=\"20\" src=\"\.\/htmls\//i", $entryContent)){
				file_put_contents("$sid/htmls/$word.html", "./htmlCollection/$word.html");
			}
	
	
		} else {
	
			$url = "";
			if(preg_match("/www.oxfordlearnersdictionaries.com/i", $w)){
				$url = $w;
			}else{
				$url = "http://www.oxfordlearnersdictionaries.com/search/english/direct/?q=" . $w;
			}
			$content = getContent($url);
			
			// create the DOMDocument object, and load HTML from $content
			$dochtml = new DOMDocument();
			$dochtml->loadHTML($content);
			
			// create the DOMXpath object, and load HTML from $strhtml
			$xpath = new DOMXpath($dochtml);
				
			// GET WORD
			$varWords = $xpath->query('//h2[@class="h"]');
			$word = $varWords->item(0)->nodeValue;
				
			// GET WORD TYPE
			$varWordTypes = $xpath->query('//span[@class="pos"]');
			$wordType = $varWordTypes->item(0)->nodeValue;
			if(isset($wordType)){
				$wordType = "($wordType)";
			}
			
			// GET PHONETIC
			$varPhonetics = $xpath->query('//span[@class="phon"]');
			$phonetics = getPhonetics($varPhonetics);
			$phonetic = "BrE &nbsp" . $phonetics[0] . "&nbsp &nbsp &nbsp" . "NAmE &nbsp" . $phonetics[1];
				
			// GET EXAMPLES
			$varExamples = $xpath->query('//span[@class="x-g"]');
			$examples = getExamples($varExamples);
			$example = $examples[0] . $examples[1] . $examples[2] . $examples[3];
			$example = preg_replace("/$word/i", "{{c1::$word}}", $example);
			$example = '<link type="text/css" rel="stylesheet" href="./oxlayout/oxford.css">' . $example;
			//$example = '<div class="responsive_entry_center_wrap" style="padding: 5px">' . $example . '</div>';
				
			// GET PRONUNCIATION SOUND FILES
			$query_uk = $xpath->query('//div[@class="sound audio_play_button pron-uk icon-audio"]');
			$sound_uks = getSounds($query_uk, $sid);
			$sound_uk = $sound_uks[0];
			$query_us = $xpath->query('//div[@class="sound audio_play_button pron-us icon-audio"]');
			$sound_ues = getSounds($query_us, $sid);
			$sound_us = $sound_ues[0];
				
			// GET IMAGE FILES
			// get the element with tag="img"
			$imgsContentHTML = $dochtml->getElementById('ox-enlarge');
			$img = $thumb = "";
			
			$imgs = getImages($imgsContentHTML, 'a', 'href', $sid);
			if(isset($imgs[0])){
				$img = $imgs[0];
			}else{
				$img = '<a href="https://www.google.com.vn/search?biw=1280&bih=661&tbm=isch&sa=1&q=' . $word . '" style="font-size: 15px; color: blue">Images for the word: ' . $word . '</a>';		
			}
			
			$thumbs = getImages($imgsContentHTML, 'img', 'src', $sid);
			if(isset($thumbs[0])){
				$thumb = $thumbs[0];
			}else{
				$thumb = '<a href="https://www.google.com.vn/search?biw=1280&bih=661&tbm=isch&sa=1&q=' . $word . '" style="font-size: 15px; color: blue">Images for this word</a>';
			}
				
			// GET ENTRY CONTENT FROM OXFORD DICTIONARY
			// get the element with id="entryContent"
			$entryContentHTML = $dochtml->getElementById('entryContent');
			
			// uses innerHTML() to get the HTML content from $entryContent
			if (isset($entryContentHTML)){
				$entryContent = innerHTML($entryContentHTML);
				$entryContent = '<html>' .
				'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' .
				'<link type="text/css" rel="stylesheet" href="./oxlayout/interface.css">' .
				'<link type="text/css" rel="stylesheet" href="./oxlayout/responsive.css">' .
				'<link type="text/css" rel="stylesheet" href="./oxlayout/oxford.css">' .
				'<div id="entryContent" class="responsive_entry_center_wrap">' . 	
				$entryContent . '</div>' . '</html>';
			} else {
				$entryContent = "THIS WORD DOES NOT EXIST...!" + "[" + $word + "]";
			}
			
			
			
			$entryContent = preg_replace("/\t/i", ' ', $entryContent);
			$entryContent = preg_replace("/\n/i", ' ', $entryContent);
			$entryContent = preg_replace("/class=\"unbox\"/i", 'class="unbox is-active"', $entryContent);
			
			if(strlen($entryContent)>131072){
				file_put_contents("./htmlCollection/$word.html", $entryContent);
				file_put_contents("$sid/htmls/$word.html", $entryContent);
				$entryContent = '<iframe marginwidth="20" marginheight="20" src="./htmls/' . $word . '.html' . '" width=800 height=500/></iframe>';
			}
			
			// GET TAG FROM WORD
			$tag = $word[0];
				
			// GET COPYRIGHT
			$copyRight = "This flashcard's content is get from the Oxford Advanced Learner's Dictionary.<br>Thanks Oxford Dictionary! Thanks for using!";
			
			// GET FULL CONTENT FOR EACH CARD
			$ankiDeck = $word . "\t" . $wordType . "\t" . $phonetic . "\t" . $example . "\t" . $sound_uk . "\t" . $sound_us . "\t" . $thumb . "\t" . $img . "\t" . $entryContent . "\t" . $copyRight . "\t" . $tag . "\n";

			if($word) {
				echo $ankiDeck;

				file_put_contents("$sid/ankiDeck.csv", $ankiDeck, FILE_APPEND | LOCK_EX);
				$word = preg_replace("/\"/i", "\\\"", $word);
				$wordType = preg_replace("/\"/i", "\\\"", $wordType);
				$phonetic = preg_replace("/\"/i", "\\\"", $phonetic);
				$example = preg_replace("/\"/i", "\\\"", $example);
				$sound_uk = preg_replace("/\"/i", "\\\"", $sound_uk);
				$sound_us = preg_replace("/\"/i", "\\\"", $sound_us);
				$thumb = preg_replace("/\"/i", "\\\"", $thumb);
				$img = preg_replace("/\"/i", "\\\"", $img);
				$entryContent = preg_replace("/\"/i", "\\\"", $entryContent);
				$tag = preg_replace("/\"/i", "\\\"", $tag);
				$copyRight = preg_replace("/\"/i", "\\\"", $copyRight);
				$ankiDeck = preg_replace("/\"/i", "\\\"", $ankiDeck);
		
				$sql = "INSERT INTO $table (flsWord, flsWordType, flsPhonetic, flsExample, flsSound_uk, flsSound_us, flsThumb, flsImg, flsEntryContent, flsTag, flsCopyRight, flsAnkiDeck, flsLastQueryTime) VALUES (\"$word\", \"$wordType\", \"$phonetic\", \"$example\", \"$sound_uk\", \"$sound_us\", \"$thumb\", \"$img\", \"$entryContent\", \"$tag\", \"$copyRight\", \"$ankiDeck\", NOW())";
	
				if ($conn->query($sql) === TRUE) {
					file_put_contents("./writedata_$sid.log", "New record is created successfully for this word [$word].\n$ankiDeck.\n", FILE_APPEND | LOCK_EX);
				} else {
					file_put_contents("./writedata_$sid.log", "Error: $sql.\n$conn->error.\n", FILE_APPEND | LOCK_EX);
				}
			} else {
				echo "Cannot create flashcard for this input [$w].\n";
			}
		}
	}
	$conn->close();
}




// SUB FUNCTIONS

// returns a string with the HTML content from a DOMDocument node element ($elm)
function innerHTML(DOMNode $elm) { 
	$innerHTML = ''; 
	$children  = $elm->childNodes;
	
	foreach($children as $child) { 
		$innerHTML .= $elm->ownerDocument->saveHTML($child);
	}
	return $innerHTML;
}

// returns a string with the content of $url
function getContent($url) {
	$opts = array(
		'http' => array(
		//'proxy' => 'tcp://192.168.103.62:3128',
		'request_fulluri' => True,
		),
	);
	
	$context = stream_context_create($opts);
	$content = file_get_contents($url, true, $context);
	return $content;
}

// returns a array of sound for flashcard & put sound files into folder
function getSounds($varSounds, $sid) {
	$sounds = array();
	$sound = "";
	if (isset($varSounds)){
		foreach($varSounds as $item) {
			$mp3Source = $item->getAttribute("data-src-mp3");
			$mp3FileContent = getContent($mp3Source);
			$mp3SourceSplited = explode('/',$mp3Source);
			$mp3SourceFileName = end($mp3SourceSplited);
			file_put_contents("./soundCollection/$mp3SourceFileName", $mp3FileContent);
			file_put_contents("$sid/sounds/$mp3SourceFileName", $mp3FileContent);
			$sound = "[sound:./sounds/$mp3SourceFileName]";
			array_push($sounds, $sound);
		}
	} else {
		$sounds = array();
	}
	return $sounds;
}

// returns a array of images for flashcard & put image files into folder
function getImages($parent, $tag, $att, $sid) {
	$imgs = array();
	$img = "";
	
	if (isset($parent)){
	$imgNodes = $parent->getElementsByTagName($tag);

		foreach($imgNodes as $item) {
			$imgSource = $item->getAttribute($att);
			$imgFileContent = getContent($imgSource);
			$imgSourceSplited = explode('/',$imgSource);
			if($tag==a){
				$imgSourceFileName = 'fullsize_' . end($imgSourceSplited);
			}else{
				$imgSourceFileName = end($imgSourceSplited);
			}
			file_put_contents("./imageCollection/$imgSourceFileName", $imgFileContent);
			file_put_contents("$sid/images/$imgSourceFileName", $imgFileContent);
			$img = '<img src="' . "./images/$imgSourceFileName" . '"/>';
			array_push($imgs, $img);
		}
	}else{
		$imgs = array();
	}
	return $imgs;
}

// returns a array of phonetics for flashcard
function getPhonetics($varPhonetics){
	$phonetics = array();
	$phonetic = "";
	if (isset($varPhonetics)){
		foreach($varPhonetics as $item) {
			$phonetic = innerHTML($item);
			$phonetic = '<span class="phon">' . $phonetic . '</span>';
			$phonetic = preg_replace("/<span class=\"wrap\">\/<\/span>/i", '', $phonetic);
			$phonetic = preg_replace("/<span class=\"bre\">BrE<\/span>/i", '', $phonetic);
			$phonetic = preg_replace("/<span class=\"name\">NAmE<\/span>/i", '', $phonetic);
			array_push($phonetics, $phonetic);
		}
	} else {
		$phonetics = array("There is no phonetic for this word!");
	}
	return $phonetics;
}


// returns a array of phonetics for flashcard
function getExamples($varExamples){
	$examples = array();
	$example = "";
	if (isset($varExamples)){
		foreach($varExamples as $item) {
			$example = innerHTML($item);
			$example = '<div class="x-g">' . $example . '</div>';
			array_push($examples, $example);
		}
	} else {
		$examples = array("There is no example for this word!");
	}
	return $examples;
}


// clear result
function recursiveRemoveDirectory($directory)
{
    foreach(glob("{$directory}/*") as $file)
    {
        if(is_dir($file)) { 
            recursiveRemoveDirectory($file);
        } else {
            unlink($file);
        }
    }
    rmdir($directory);
}

// zip result keep source structure
function zpr( array $sources, $archivName, $destination, $zipObject){
	foreach($sources as $source){
		$files = array_diff(scandir($source), array('..', '.'));
		foreach($files as $file){			
			$src = "$source/$file";
			$des = "$destination/$src";
			if(is_dir($src)){
				$arraysrc = array($src);
				zpr($arraysrc, $archivName, $destination, $zipObject);
			}else{
				$zipObject->addFromString($des, file_get_contents($src));
			}
		}
	}
}

// zip result 2
function zpr2( array $sources, $archivName, $destination, $zipObject){
	foreach($sources as $source){
		$files = array_diff(scandir($source), array('..', '.'));
		foreach($files as $file){			
			$src = "$source/$file";
			$des = "$destination/$file";
			if(is_dir($src)){
				$arraysrc = array($src);
				zpr2($arraysrc, $archivName, $destination, $zipObject);
			}else{
				$zipObject->addFromString($des, file_get_contents($src));
			}
		}
	}
}

// zip result 3
function zpr3( array $sources, $archivName, $destination, $zipObject, $sid){
	foreach($sources as $source){
		$files = array_diff(scandir($source), array('..', '.'));
		foreach($files as $file){			
			$src = "$source/$file";
			$des = "$destination/$src";
			if(is_dir($src)){
				$arraysrc = array($src);
				zpr3($arraysrc, $archivName, $destination, $zipObject, $sid);
			}else{
				$des = preg_replace("/$sid\//i", '', $des);
				$zipObject->addFromString($des, file_get_contents($src));
			}
		}
	}
}

?>