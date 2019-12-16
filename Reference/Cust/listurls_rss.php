<?PHP

//Eg http://spinningmonkey.com/Cust/listurls_rss.php?url=https%3A%2F%2Fwww.cbc.ca%2Fcmlink%2Frss-world

header('Content-Type: text/plain');
  
  $urlRaw = $_GET['url'];
  $url = urldecode($urlRaw);
  $nthUrl = $_GET['index'];
  $index = intval($nthUrl);
  $input = @file_get_contents($url) or die("Could not access file: $url");

class RssUrlHelper{

	public function Remove($raw,$what){
		$ret = str_replace($what,"",$raw);
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
$url = $rssH->getNthUrl($input,$index);

    echo("index: " . strval($index));
	echo($url);
	
?>