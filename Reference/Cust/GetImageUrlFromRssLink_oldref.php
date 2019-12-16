<?PHP

//Eg http://spinningmonkey.com/Cust/GetImageUrlFromRssLink.php?url=https%3A%2F%2Fwww.cbc.ca%2Fcmlink%2Frss-world&imageindex=0

//GetImageUrlFromRssLink.php
  
  $urlRaw = $_GET['url'];
  $url = urldecode($urlRaw);
  $nthUrl = $_GET['index'];
  //$index = intval($nthUrl);
  $imageIndex = $_GET['imageindex'];
  $input = @file_get_contents($url) or die("Could not access file: $url");

class RssUrlHelper{

	public function Remove($raw,$what){
		$ret = str_replace($what,"",$raw);
		return $ret;
	}
	
	public function getFileExtension($imageurl){
		//https://i.cbc.ca/1.5376021.1574968299!/fileImage/httpImage/image.jpg_gen/derivatives/16x9_780/jamaicapothole4.jpg
		$splits = explode(".",$imageurl);
		$lastIndex = count($splits)-1;
		$imageExtensionTouse = "";
		if($lastIndex>=0){
			$imageExtensionTouse = $splits[$lastIndex];
		}
		return $imageExtensionTouse;
	}
	
	public function getImageMimeType($imageExtensionTouse){
		$mtype = "";
			switch( $imageExtensionTouse ) {
				case "png": $mtype="image/png"; break;
				case "jpeg":
				case "jpg": $mtype="image/jpeg"; break;
				default:"";
			}
			return $mtype;
	}
	
	
	public function getNthImage($input,$index){
		$ret = "";
		preg_match_all('/<img[^>]+>/i',$input, $matches);
		/*
		$matches[0] => array of image paths (as in source code)
		$matches[1] => array of file names
		$matches[2] => array of extensions
		*/
		$allImages = $matches[0];
		try{
			$ret = $allImages[$index];
			if($ret == ""){
				$ret = "Cannot find images in markup";
			}
		}catch(Exception $e){
			$ret="Cannot find an image due to error: " . $e->getMessage();
		}
		return $ret;
	}
	
	public function getNthUrl($input,$index){
		$ret = 'No Url found in markup';
		if(preg_match_all("#<\s*?link\b[^>]*>(.*?)</link\b[^>]*>#s", $input, $matches, PREG_PATTERN_ORDER)) {
			$match = $matches[$index];
			$address = $this->Remove($match[2],"<link>");
			$address = $this->Remove($address,"</link>");
			$ret = $address;
		}
		return $ret;
	}
}

$rssH = new RssUrlHelper();
$linkUrl = $rssH->getNthUrl($input,0);
header('Content-Type: text/plain');//only when reporting text-based details
    //echo("index: " . strval($index)) ."\n";
	//echo($url);//ok
	//follow that url for its markup and grab the first image tag url
	$pagemarkup = @file_get_contents($url) or die("Could not access file: $url");
	//echo($pagemarkup);
	$imageUrl = $rssH->getNthImage($pagemarkup,strval($imageIndex));
	echo($imageUrl);
	//$extension = $rssH->getFileExtension($imageUrl);
	//$mimeType = $rssH->getImageMimeType($extension);
	//echo($imageUrl);
	//now display the image, setting the header type to whetever the extension says it should be
	//header('Content-Type: '.$mimeType);
	//readfile($imageUrl);//present the actual image to the page
?>