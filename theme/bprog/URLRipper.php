<?PHP namespace brog;

class UrlDetails{
    public $title = null;
    public $url = null;
}


class URLRipper{
    public $ut = null;

    public function __construct($ut)
		{
            //echo("URL ripper constructor called"."<br/>");
            if(is_null($ut)){
                $ut = new BPUtil();
                $this->ut=$ut;
            }
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
        
        public function getImageTagsArrayFromHtmlMarkup($markupString, $tag, $attr){
            $result = new ResultOfTask();
            $obj = 'No Url found in markup';
            $state = false;
            $extractedImages = array();

            $dom = new \DOMDocument;
            @$dom->loadHTML($markupString);
            $tags = $dom->getElementsByTagName($tag);
            foreach ($tags as $tag){
                $attrib = $tag->getAttribute($attr);
                $extractedImages[]=$attrib;
            }

            $result->obj =$extractedImages;
            $result->state = $state;
            return $result;
        }

        public function getImageTagsArrayFromHtmlMarkupOld($markupString, $tag){
			$result = new ResultOfTask();
			$obj = 'No Url found in markup';
            $state = false;
            $extractedImages = array();
            echo("attempting to find images inside " . $tag . "tags");
            $nodes = $this->extract_tags( $markupString, $tag );
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

?>