<?php

function dolog($txt){
	file_put_contents('logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
}


//eg http://spinningmonkey.com/Cust/imageFromRss.php?url=https%3A%2F%2Fwww.cbc.ca%2Fcmlink%2Frss-world&index=0
//index is optional - todo.
   $urlRaw = $_GET['url'];
   $imageExtension1= "jpg";//length of 3
   $imageExtension2 = "jpeg";//length of 4
   $imageExtension3= "png";//length of 3
   $url = urldecode($urlRaw);// eg https://www.cbc.ca/cmlink/rss-world
   $markup = file_get_contents($url);
   
   $followHyperlinkForImage = false;
   $followHyperlinkparam = "false";//default is 'false'
   
   if (isset($_GET['followhyper']))
{
	$followHyperlinkparam = $_GET['followhyper'];
	$followHyperlinkForImage = $followHyperlinkparam ==="true"?true:false;
}
dolog("Follow hyperlink set to ". $followHyperlinkForImage?"true":"false");
if($followHyperlinkForImage){
	echo("Need to follow first hyperlink for image");
	//could display a default image here
}else{
   $splits = explode("<img src=",$markup);
   $hasImage = $splits[1];//starts with 'http...
   //echo("hasImage:".$hasImage);
   //find in $hasImage the position in the string where any of the $imageTypes are - that will alow us to extract the image
   $positionofImageExtension1 = strpos($hasImage,$imageExtension1);
   $lengthOfImageExtension1 = 3;
   $positionofImageExtension2 = strpos($hasImage,$imageExtension2);
   $lengthOfImageExtension2 = 4;
   $positionofImageExtension3 = strpos($hasImage,$imageExtension3);
   $lengthOfImageExtension3 = 4;
   $indexOfImageToUse = 0;
   $lengthOfExtensionTouse = 0;
   $imageExtensionTouse = "jpg";//default
   if($positionofImageExtension1>=0){
	   $indexOfImageToUse = $positionofImageExtension1;
	   $lengthOfExtensionTouse = $lengthOfImageExtension1;
	   $imageExtensionTouse =$imageExtension1;
   }else if($positionofImageExtension2 >=0){
	   $indexOfImageToUse = $positionofImageExtension2;
	   $lengthOfExtensionTouse = $lengthOfImageExtension2;
	   $imageExtensionTouse =$imageExtension2;
   }else if($positionofImageExtension3 >=0){
	   $indexOfImageToUse = $positionofImageExtension3;
	   $lengthOfExtensionTouse = $lengthOfImageExtension3;
	   $imageExtensionTouse =$imageExtension3;
   }
   $substringLength = $indexOfImageToUse + $lengthOfExtensionTouse -1;
   $imageString = substr($hasImage,1,$substringLength);
   $ctype="image/jpeg";

   switch( $imageExtensionTouse ) {
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpeg"; break;
    default:"jpg";

   }
   header('Content-Type: '.$ctype);
	readfile($imageString);
}
 
?>