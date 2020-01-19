<?PHP namespace brog;
require_once('ResultOfTask.php');
require_once('BPUtil.php');
require_once('URLRipper.php');
header('Content-Type: text/plain');
//$NL = "\n\r";
class PageParams{
    public $NL = "<br/>";
    public $debug=false;
    public $urlRaw = "No URL Specified";
    public $htmlMarkup = "No html markup available";
    public $indexImageString = NULL;
    public $mimeType = NULL;
    public $imageTagsArray = NULL;
    public $imageurl = NULL;
    public $url = NULL;
    public $ut = NULL;
    public $rr = NULL;
    public $tag = "<amp-img";
    public $attr = "src="; 
}

$pp = new PageParams();
    SetInitialVariables($pp);
    GenerateMarkup($pp);
    ExtractImageMarkup($pp);
    GetImageUrlAtIndex($pp);
    GetMimeType($pp);
    GenerateImageAtIndex($pp);
    
    
    function GetQueryStrings($pp){
        if (isset($_GET["debug"])) {
            $pp->debug = $_GET["debug"];
        }
        if (isset($_GET['url'])) {
            $pp->urlRaw = $_GET['url'];//A url encoded url
        }
        if (isset($_GET['index'])) {
            $pp->indexImageString = $_GET['index'];//The image index in the resulting markup from following the URL
        }
    }

    function SetInitialVariables($pp){
        //echo("Set initial variables".$pp->NL);
        GetQueryStrings($pp);
        $pp->url = urldecode($pp->urlRaw);
        //echo("Setting BPUtil instance".$pp->NL);
        $pp->ut = new BPUtil($debug);
        //echo("Setting URLRipper instance".$pp->NL);
        $pp->rr = new URLRipper($pp->ut);
    }

    function GenerateMarkup($pp){
        if(is_null($pp->rr)){
            echo("URLRipper instance is null :(".$pp->NL);
        }else{
            $pp->htmlMarkup = $pp->rr->getMarkupFromUrl($pp->url, true);
//echo("\n\rHtml markup from ".$pp->urlRaw . "\n\r");
            //print_r($pp->htmlMarkup);
            
        }
        
    }

    function ExtractImageMarkup($pp){
        $pp->imageTagsArray = $pp->rr->getImageTagsArrayFromHtmlMarkup($pp->htmlMarkup,"img","src")->obj;
        //echo("Image tags array retreived from page:<br/>");
        //print_r($pp->imageTagsArray);
    }

    function GetImageUrlAtIndex($pp){
        $indexImage =0;//default
        if(!is_null($pp->indexImageString)){
            $indexImage = intval($pp->indexImageString);
        }
        $lengthOfImageTagsArray = count($pp->imageTagsArray);
        if ($lengthOfImageTagsArray > 0){
            $pp->imageurl = $pp->imageTagsArray[$indexImage];
              //print_r($pp->imageurl);
        }
    }

    function GetMimeType($pp){
        //echo("\n\rGetting file extension for image url of ". $pp->imageurl);
        $fileExtension = $pp->rr->getFileExtensionFromUrl($pp->imageurl)->obj;
        $pp->mimeType = $pp->rr->getMimeTypeFromFileExtension($fileExtension)->obj;
    }

    function GenerateImageAtIndex($pp){
                
                //echo("Image at index ">$rr->indexImageString . " is " . $pp->imageurl);
                $pp->rr->renderImageResult($pp->mimeType,$pp->imageurl);
            
        
            
    }

    


?>