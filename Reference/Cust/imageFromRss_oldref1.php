<?php
 include "rssparser.php";

//eg http://spinningmonkey.com/Cust/imageFromRss.php?url=https%3A%2F%2Fwww.cbc.ca%2Fcmlink%2Frss-world
   $urlRaw = $_GET['url'];
   $url = urldecode($urlRaw);// eg https://www.cbc.ca/cmlink/rss-world
   echo($url);
   $rss_parser = new Chirp\RSSParser($url);
    $htmlMarkup = $rss_parser->getOutput();
	echo("html:");
	echo($htmlMarkup);
?>