<?PHP

//Eg http://spinningmonkey.com/Cust/listurls.php?url=https%3A%2F%2Fwww.cbc.ca%2Fcmlink%2Frss-world

  //ini_set('user_agent', 'NameOfAgent (http://www.example.net)');

header('Content-Type: text/plain');
  
  $urlRaw = $_GET['url'];
  $url = urldecode($urlRaw);
  
  
  //if(robots_allowed($url, "NameOfAgent")) {
	  
    $input = @file_get_contents($url) or die("Could not access file: $url");
    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
	
    if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
      listMatches($matches);
    }else{

		if(preg_match("/<link>(.*?)</link>/s", $input, $matches)) {
			listMatches($matches);
		}else{
			echo("No urls located at " . $urlRaw);
			echo("Its markup:");
			echo($input);
		}
	}
	
  //} else {
   // die('Access denied by robots.txt');
  //}
  
  function listMatches($matches){
	  
      foreach($matches as $match) {
        $address = $match[2];
        $text = $match[3];
		echo("Address: ".$address);
		echo("Text: ".$text);
      }
    
  }
?>