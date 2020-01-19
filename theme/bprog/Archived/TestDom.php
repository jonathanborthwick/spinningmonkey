<?php
header('Content-Type: text/plain');
echo("TESTING LOADING DOM \n\r");

//Send a GET request to the URL of the web page using file_get_contents.
//This will return the HTML source of the page as a string.
$htmlString = file_get_contents('https://en.wikipedia.org/wiki/Main_Page');
 
//Create a new DOMDocument object.
$htmlDom = new DOMDocument;
 
//Load the HTML string into our DOMDocument object.
@$htmlDom->loadHTML($htmlString);
 
//Extract all img elements / tags from the HTML.
$imageTags = $htmlDom->getElementsByTagName('img');
 
//Create an array to add extracted images to.
$extractedImages = array();
 
//Loop through the image tags that DOMDocument found.
foreach($imageTags as $imageTag){
 
    //Get the src attribute of the image.
    $imgSrc = $imageTag->getAttribute('src');
 
    //Get the alt text of the image.
    $altText = $imageTag->getAttribute('alt');
 
    //Get the title text of the image, if it exists.
    $titleText = $imageTag->getAttribute('title');
 
    //Add the image details to our $extractedImages array.
    $extractedImages[] = array(
        'src' => $imgSrc,
        'alt' => $altText,
        'title' => $titleText
    );
}
 
//var_dump our array of images.
var_dump($extractedImages);
?>