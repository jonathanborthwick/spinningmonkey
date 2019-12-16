<?PHP
$urlRaw = $_GET['url'];
$index1 = $_GET['index1'];//the link index in the rss feed
$index2 = $_GET['index2'];//the image index in the resulting markup from following thaty rss feed link
header('Content-Type: text/plain');
/*
http://spinningmonkey.com/Cust/GetImageFromRssHtml.php?url=http%3A%2F%2Frss.cnn.com%2Frss%2Fcnn_topstories.rss&index1=0&index2=0
get url
get index in rss feed
get index of image in first link
get markup of the rss feed
find all urls in rss feed markup
get markup from the url whos index is specified or the first one if it goes out of bounds
find all image tags in that markup
extract the url for the image between the tags
get the file extension of the image file
get the mime type of the image
set the page header to the image type
do a readfile withthe url oif the image - will make the page be the actual image
*/

class ResultOfTask{
	public $state;
	public $obj; 
	public function __construct()
    {
		$this->state=false;
		$this->obj="";
    }
}

class RSSRipper{
	
	function startsWith($haystack, $needle)
	{
		 $length = strlen($needle);
		 return (substr($haystack, 0, $length) === $needle);
	}
	
	function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
	
		return (substr($haystack, -$length) === $needle);
	}

	/**
 	* Reference https://phpro.org/examples/Get-Text-Between-Tags.html
 	* @get text between tags using regex (May want to improve later to use domDocument method)
 	*
 	* @param string (The string with tags)
 	*
 	* @param string $tagname (the name of the tag
 	*
 	* @return string (Text between tags)
 	*
 	*/
	public function getTextBetweenTags($string, $tagname)
	{
   		$pattern = "/<$tagname>(.*?)<\/$tagname>/";
   		preg_match($pattern, $string, $matches);
   		return $matches[1];
	}
	
	public function echoMessage($message,$setHeader){
		if($setHeader){
			header('Content-Type: text/plain');
		}
		echo($message);
	}
	
	public function getMarkupFromUrl($urlString){

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $urlString); 
		curl_setopt($ch, CURLOPT_HEADER, FALSE); 
		curl_setopt($ch, CURLOPT_NOBODY, FALSE); 
		curl_setopt($ch,CURLOPT_TIMEOUT,30); // TIME OUT is 5 seconds
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		$response = curl_exec($ch); 
		
		curl_close($ch); 

		return $response;

		// $arrContextOptions=array(
		// 	"ssl"=>array(
		// 		  "verify_peer"=>false,
		// 		  "verify_peer_name"=>false,
		// 	  ),
		//   );  
		// $input = @file_get_contents($urlString,false, stream_context_create($arrContextOptions)) or die("Could not access file: ".$urlString);

		//return $input;
	}


	public function getUrlsFromRssMarkup($markupString){
		$result = new ResultOfTask();
		$obj = 'No Url found in markup: ';
		$obj = $obj.$markupString;

		//explode the markup based on link <link>
		$splits1 = explode("<link>",$obj);
		$arrayofUrls = [];
		foreach ($splits1 as $piece){
			if($this->startsWith($piece,'http')){
				//everything here up to before </ goes into $arrayofUrls
				$endLinkPosition =strpos($piece,'</');
				if($endLinkPosition >=0){
					$thisUrl = substr($piece,0,$endLinkPosition);
					array_push($arrayofUrls,$thisUrl);
				}
			}	
		}
		if(sizeof($arrayofUrls)>0){
			$result->obj = $arrayofUrls;
			$result->state = true;
		}else{
			$result->obj =$obj;
		}
		
		// if(preg_match_all("#<\s*?link\b[^>]*>(.*?)</link\b[^>]*>#s", $input, $matches, PREG_PATTERN_ORDER)) {//problematic regex, as its not finding stuff that is clearly there.. see ouput of calling the example url 
		// 	$obj = $matches;
		// 	$state = true;
		// }


		return $result;
	}

	public function getNthUrlFromList($urlListOfString,$indexInt){
		$result = new ResultOfTask();
		$match ="";
		$anyError = "";
		try{
			$match = $urlListOfString[$indexInt];
		}catch(Exception $e){
			$anyError = $e->getMessage();
		}		
		if($anyError ==""){
			$address = str_replace("<link>","",$match[2]);
			$address = str_replace("</link>",$address);
			$result->state=true;
			$result->obj = $address;
		}else{
			$result->obj="Cannot find url in list with index " . strval($indexInt) . ".\n\r" . $anyError;
		}
		
		return $result;
	}

	public function getImageTagsFromHtmlMarkup($markupString){
		$result = new ResultOfTask();
		$obj = 'No Url found in markup';
		$state = false;
		if(preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i',$html, $matches )){
			$obj = $matches;
			$state = true;
		}; 
		$result->state = $state;
		$result->obj =$obj;
		return $result;
	}
	/**reference: https://phpro.org/examples/Get-Text-Between-Tags.html
	 * may want to redo getTextBetweenTags function to use domElement method instead of regex
	 */
	public function getNthImageFromTags($tagsArray,$indexInt){
		$result = new ResultOfTask();
		$obj = "Cannot find an image in tags array";
		try{
			$obj = $tagsArray[$indexInt];//still has <img> tags around it. strip them out oif they exist
			//get everything in between the img tags
			$content = $this->getTextBetweenTags('img',$obj);
			$result->obj = $content;
			$result->state = true;
		}catch(Exception $e){
			$anyError = $e->getMessage();
			$obj = $obj."\n".$anyError;
			$result->obj = $obj;
		}
		return $result;
	}
	public function getFileExtensionFromUrl($urlString){
		$result = new ResultOfTask();
		try{
			$ext = pathinfo($urlString, PATHINFO_EXTENSION);
			$result->state = true;
			$result->obj = $ext;
		}catch(Exception $e){
			$anyError = $e->getMessage();
			$result->obj = $anyError;
		}
		return $result;
	}
	public function getMimeTypeFromFileExtension($fileExtensionString){
		$result = new ResultOfTask();
		$mtype = "";

			switch( $fileExtensionString ) {
				case "png": $mtype="image/png"; break;
				case "jpeg":
				case "jpg": $mtype="image/jpeg"; break;
				default:"";
			}
			if($mtype ==""){
				$result->obj = "Cannot find mime type for " . $fileExtensionString;
			}else{
				$result->obj =$mtype;
				$result->state = true;
			}

		return $result;
	}
	public function renderImageResult($mimeType,$imageUrl){
		header('Content-Type: '.$mimeType);
		readfile($imageUrl);
	}
}

$url = urldecode($urlRaw);
$rr = new RSSRipper();
$rssMarkup = $rr->getMarkupFromUrl("Initial step: ".$url);
$hasUrlListOfString = $rr->getUrlsFromRssMarkup($rssMarkup);
if($hasUrlListOfString->state){
	$urlListOfString = $hasUrlListOfString->obj;
	$hasTargetedUrlInRss = $rr->getNthUrlFromList($urlListOfString,$index1);
	if($hasTargetedUrlInRss->state){
		$targetedUrlInRss = $hasTargetedUrlInRss->obj;
		$hasHtmlMarkup = $rr->getMarkupFromUrl("Get markup from targeted url: ".$targetedUrlInRss);
		if($hasHtmlMarkup->state){
			$htmlMarkup = $hasHtmlMarkup->obj;
			$hasImageTagListOfString = $rr->getImageTagsFromHtmlMarkup($htmlMarkup);
			if($hasImageTagListOfString->state){
				$imageTagListOfString = $hasImageTagListOfString->obj;
				$hasTargetedImageInList = $rr->getNthImageFromTags($imageTagListOfString,$index2);
				if($hasTargetedImageInList->state){
					$targetedImageInList = $hasTargetedImageInList->obj;
					$hasFileExtension = $rr->getFileExtensionFromUrl($targetedImageInList);
					if($hasFileExtension->state){
						$fileExtension = $hasFileExtension->obj;
						$hasMimeType = $rr->getMimeTypeFromFileExtension($fileExtension);
						if($hasMimeType->state){
							$mimeType = $hasMimeType->obj;
							renderImageResult($mimeType,$targetedImageInList);	//final thing to do - present the image to the page
						}
					}else{
						$rr->echoMessage($hasFileExtension->obj);
					}
				}else{
					$rr->echoMessage($hasTargetedImageInList->obj);
				}
			}else{
				$rr->echoMessage($hasImageTagListOfString->obj);
			}
		}else{
			$rr->echoMessage($hasHtmlMarkup->obj);
		}
	}else{
		$rr->echoMessage($hasTargetedUrlInRss->obj);
	}
}else{
	$rr->echoMessage($hasUrlListOfString->obj);
}
?>