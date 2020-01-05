<?PHP namespace brog;
    session_start();
	//header('Content-Type: text/plain');

    /*
   http://spinningmonkey.com/wp-content/themes/SpinningMonkey/GetImageFromRssHtml.php?url=http%3A%2F%2Frss.cnn.com%2Frss%2Fcnn_topstories.rss&indexurl=2&indexthumb=1
    get url
	get index in RSS feed
	get index of image in first link
	get markup of the RSS feed
	find all urls in RSS feed markup
	get markup from the url whos index is specified or the first one if it goes out of bounds
	find all image tags in that markup
	extract the url for the image between the tags
	get the file extension of the image file
	get the mime type of the image
	set the page header to the image type
	do a readfile withthe url oif the image - will make the page be the actual image
	*/

    $hasError = false;
    $urlRaw = "No RSS feed URl specified";
    $rssMarkup = "No markup available for url specified";
    $htmlMarkup = "No html markup available";
    $feedsrc = "";
    $debug=false;
    if (isset($_GET['url'])) {
        $urlRaw = $_GET['url'];//A url encoded url of an RSS feed
    }else{
        $hasError = true;
    }
    $indexurl =  0;//default to the first instance of a url found in the rss feed
    if (isset($_GET['indexurl'])) {
        $indexurl = $_GET['indexurl'];//The index of a link in the RSS feed 
    }
    $indexthumb = 0;
    if (isset($_GET['indexthumb'])) {
        $indexthumb = $_GET['indexthumb'];//The image index in the resulting markup from following that RSS feed link
    }
    if(isset($_GET["feedsrc"])){
        $feedsrc = $_GET["feedsrc"];
    }
    if(isset($_GET["debug"])){
        $debug = $_GET["debug"];
    }
   
	class ResultOfTask{
		public $state;
		public $obj; 
		public function __construct()
		{
			$this->state=false;
			$this->obj="";
		}
	}

	class BPUtil{
        private $debug = false;
        public function __construct($debug)
		{
            $this->debug = $debug;
        }
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
        function echoNl($text){
            if($this->debug){
                //echo("\n\r----------------- DEBUG IS TRUE HERE--------------\n\r");
                echo($text . "\n\r");
            }
        }
	}

	class RSSRipper{

        private $ut = null;
        public function __construct($ut)
		{
            if(is_null($ut)){
                $ut = new BPUtil();
            }
            $this->ut=$ut;
            
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
		
		public function getMarkupFromUrl($Url, $useCurl){
            $response = "";
            if($useCurl){
                $ch = curl_init(); 
                 curl_setopt($ch, CURLOPT_URL, $Url); 
                 curl_setopt($ch, CURLOPT_HEADER, FALSE); 
                 curl_setopt($ch, CURLOPT_NOBODY, FALSE); 
                 curl_setopt($ch,CURLOPT_TIMEOUT,30); // TIME OUT is 5 seconds
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
                 $response = curl_exec($ch); 
                 curl_close($ch); 
            }else{
                $response = file_get_contents($Url);
            }
             return $response;
		}

		public function getUrlsFromRssMarkup($markupString){
			$result = new ResultOfTask();
			$obj = 'No Url found in markup: ';
			$obj = $obj.$markupString;

			//explode the markup based on link <link>
			$splits1 = explode("<link>",$obj);
			$arrayofUrls = [];
			foreach ($splits1 as $piece){
				if($this->ut->startsWith($piece,'http')){
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
				$address = str_replace("</link>","",$address);
				$result->state=true;
				$result->obj = $address;
			}else{
				$result->obj="Cannot find url in list with index " . strval($indexInt) . ".\n\r" . $anyError;
			}
			
			return $result;
		}

		public function getImageTagsFromHtmlMarkupOld($markupString){
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

/**
 * extract_tags()
 * Extract specific HTML tags and their attributes from a string.
 *
 * You can either specify one tag, an array of tag names, or a regular expression that matches the tag name(s). 
 * If multiple tags are specified you must also set the $selfclosing parameter and it must be the same for 
 * all specified tags (so you can't extract both normal and self-closing tags in one go).
 * 
 * The function returns a numerically indexed array of extracted tags. Each entry is an associative array
 * with these keys :
 *  tag_name    - the name of the extracted tag, e.g. "a" or "img".
 *  offset      - the numberic offset of the first character of the tag within the HTML source.
 *  contents    - the inner HTML of the tag. This is always empty for self-closing tags.
 *  attributes  - a name -> value array of the tag's attributes, or an empty array if the tag has none.
 *  full_tag    - the entire matched tag, e.g. '<a href="http://example.com">example.com</a>'. This key 
 *                will only be present if you set $return_the_entire_tag to true.      
 *
 * @param string $html The HTML code to search for tags.
 * @param string|array $tag The tag(s) to extract.                           
 * @param bool $selfclosing Whether the tag is self-closing or not. Setting it to null will force the script to try and make an educated guess. 
 * @param bool $return_the_entire_tag Return the entire matched tag in 'full_tag' key of the results array.  
 * @param string $charset The character set of the HTML code. Defaults to ISO-8859-1.
 *
 * @return array An array of extracted tags, or an empty array if no matching tags were found. 
 */
function extract_tags( $html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'ISO-8859-1' ){
     
    if ( is_array($tag) ){
        $tag = implode('|', $tag);
    }
     
    //If the user didn't specify if $tag is a self-closing tag we try to auto-detect it
    //by checking against a list of known self-closing tags.
    $selfclosing_tags = array( 'area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param' );
    if ( is_null($selfclosing) ){
        $selfclosing = in_array( $tag, $selfclosing_tags );
    }
     
    //The regexp is different for normal and self-closing tags because I can't figure out 
    //how to make a sufficiently robust unified one.
    if ( $selfclosing ){
        $tag_pattern = 
            '@<(?P<tag>'.$tag.')           # <tag
            (?P<attributes>\s[^>]+)?       # attributes, if any
            \s*/?>                   # /> or just >, being lenient here 
            @xsi';
    } else {
        $tag_pattern = 
            '@<(?P<tag>'.$tag.')           # <tag
            (?P<attributes>\s[^>]+)?       # attributes, if any
            \s*>                 # >
            (?P<contents>.*?)         # tag contents
            </(?P=tag)>               # the closing </tag>
            @xsi';
    }
     
    $attribute_pattern = 
        '@
        (?P<name>\w+)                         # attribute name
        \s*=\s*
        (
            (?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)    # a quoted value
            |                           # or
            (?P<value_unquoted>[^\s"\']+?)(?:\s+|$)           # an unquoted value (terminated by whitespace or EOF) 
        )
        @xsi';
 
    //Find all tags 
    if ( !preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ){
        //Return an empty array if we didn't find anything
        return array();
    }
     
    $tags = array();
    foreach ($matches as $match){
         
        //Parse tag attributes, if any
        $attributes = array();
        if ( !empty($match['attributes'][0]) ){ 
             
            if ( preg_match_all( $attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER ) ){
                //Turn the attribute data into a name->value array
                foreach($attribute_data as $attr){
                    if( !empty($attr['value_quoted']) ){
                        $value = $attr['value_quoted'];
                    } else if( !empty($attr['value_unquoted']) ){
                        $value = $attr['value_unquoted'];
                    } else {
                        $value = '';
                    }
                     
                    //Passing the value through html_entity_decode is handy when you want
                    //to extract link URLs or something like that. You might want to remove
                    //or modify this call if it doesn't fit your situation.
                    $value = html_entity_decode( $value, ENT_QUOTES, $charset );
                     
                    $attributes[$attr['name']] = $value;
                }
            }
             
        }
         
        $tag = array(
            'tag_name' => $match['tag'][0],
            'offset' => $match[0][1], 
            'contents' => !empty($match['contents'])?$match['contents'][0]:'', //empty for self-closing tags
            'attributes' => $attributes, 
        );
        if ( $return_the_entire_tag ){
            $tag['full_tag'] = $match[0][0];            
        }
          
        $tags[] = $tag;
    }

    //https://w-shadow.com/blog/2009/10/20/how-to-extract-html-tags-and-their-attributes-with-php/
     
    return $tags;
}

        public function getImageTagsArrayFromHtmlMarkup($markupString){
			$result = new ResultOfTask();
			$obj = 'No Url found in markup';
            $state = false;
            $extractedImages = array();
            $nodes = $this->extract_tags( $markupString, 'img' );
            foreach($nodes as $img){
                $extractedImages[]=$img;
            }
            if(sizeof($extractedImages)>0){
                $obj = $extractedImages;
                $state =true;
            }

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
				$content = $this->ut->getTextBetweenTags('img',$obj);
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

	//Eg CNN World News
	//http://rss.cnn.com/rss/cnn_topstories.rss
	//URL Encoded (How it comes in here)
	//http%3A%2F%2Frss.cnn.com%2Frss%2Fcnn_topstories.rss
$url = urldecode($urlRaw);
$ut = new BPUtil($debug);
$ut->echoNl("URL passed in:" . $url);
if(!$hasError){
    $ut->echoNl("The index of a link in the RSS feed: " . $indexurl);
    $ut->echoNl("The index of a link in the RSS feed: " . $indexthumb);
    $rr = new RSSRipper($ut);
    $ut->echoNl("RSSRipper instance created");
    if(!isset($_SESSION[$url])){
        $rssMarkup = $rr->getMarkupFromUrl($url,true);
        $_SESSION[$url] = $rssMarkup;
    }else{
        $ut->echoNl("Already retrieved markup from RSS feed at " . $url . "; using that.");
        $rssMarkup = $_SESSION[$url];
    }
    
    $ut->echoNl("Retrieved the following markup of the RSS feed:".$rssMarkup);
    $ut->echoNl($rssMarkup);
    $hasUrlListOfString = $rr->getUrlsFromRssMarkup($rssMarkup);
    if($debug){

        print_r($hasUrlListOfString);
    }

    $ut->echoNl("");
     if($hasUrlListOfString->state){
        $ut->echoNl("Retrieved List of urls from RSS feed markup:");
        $urlListOfString = $hasUrlListOfString->obj;
        if($debug){
            print_r($urlListOfString);
        }
        
     	$targetedUrlInRss =$urlListOfString[$indexurl];
        $ut->echoNl("Retrieved targeted url fromn this list at index " . $indexurl . ":");
        $ut->echoNl($targetedUrlInRss);
        $ut->echoNl("Retrieving html markup from URl:");
        $htmlMarkup = $rr->getMarkupFromUrl($targetedUrlInRss,false);//dont use curl
        $ut->echoNl($htmlMarkup);
        $ut->echoNl("Attempting to get image tags from html markup:");
        $hasImageTagListOfString = $rr->getImageTagsArrayFromHtmlMarkup($htmlMarkup);
        $imageTagListOfString = null;
        if($hasImageTagListOfString->state){
            $ut->echoNl("Printing image list from the html markup retrieved:");
            $imageTagListOfString = $hasImageTagListOfString->obj;
        if($debug){
                print_r($imageTagListOfString);
        }
        }else{
            $ut->echoNl("Could not retrieve image list from html markup:".$hasImageTagListOfString->obj);
            $ut->echoNl("Markup scanned for urls to use:");
            $ut->echoNl($htmlMarkup);
        }

        $hasTargetedImageUrl = "";
        $imageSrc = "";
        if(!is_null($imageTagListOfString)){
            $hasTargetedImageUrl = $imageTagListOfString[$indexthumb];
            $attr = $hasTargetedImageUrl["attributes"];
            $imageSrc = $attr["src"];
        }
        $ut->echoNl("Found src of image to use at index " . $indexthumb .": ".$imageSrc);
if($ut->startsWith($imageSrc,"//")){
    $imageSrc = "http:".$imageSrc;
}
$hasFileExtension = $rr->getFileExtensionFromUrl($imageSrc);
if($hasFileExtension->state){
    $fileExtension = $hasFileExtension->obj;
    $hasMimeType = $rr->getMimeTypeFromFileExtension($fileExtension);
    if($hasMimeType->state){
        $mimeType = $hasMimeType->obj;
        $rr->renderImageResult($mimeType,$imageSrc);
    }
}

     }else{
         echo("\n\rError = see echos");
     }
    }
?>