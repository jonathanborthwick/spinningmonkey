<?php namespace brog;

   /*
      http://spinningmonkey.com/wp-content/themes/SpinningMonkey/GetImageFromCBCRssHtml.php?url=http%3A%2F%2Frss.cnn.com%2Frss%2Fcnn_topstories.rss&indexurl=2&indexthumb=1
   */

    require_once('ResultOfTask.php');
    require_once('BPUtil.php');
    require_once('RSSRipper.php');
    session_start();

    // ----------------- CREATE AND INITIALIZE INITIAL VARIABLES 
    $HasNourl = false;
    $urlRaw = "No RSS feed URl specified";
    $rssMarkup = "No markup available for url specified";
    $htmlMarkup = "No html markup available";
    $feedsrc = "";
    //CREATE DEBUG VARIABLE AS FAL - TO BE CHANGED BY A QUERY STRING, ALOWING ECHO STATEMENTS TO BE OUTUT TO SCREEN
    $debug=false;

    // -----------------            GRAB QUERY STRINGS           
    if (isset($_GET['url'])) {
        $urlRaw = $_GET['url'];//A url encoded url of an RSS feed
    } else {
        $HasNourl = true;
    }
    $indexurl =  0;//default to the first instance of a url found in the rss feed
    if (isset($_GET['indexurl'])) {
        $indexurl = $_GET['indexurl'];//The index of a link in the RSS feed
    }
    $indexthumb = 0;
    if (isset($_GET['indexthumb'])) {
        $indexthumb = $_GET['indexthumb'];//The image index in the resulting markup from following that RSS feed link
    }
    if (isset($_GET["feedsrc"])) {
        $feedsrc = $_GET["feedsrc"];
    }
    if (isset($_GET["debug"])) {
        $debug = $_GET["debug"];
    }

    // ----------------- ONLY PROCEED IF THERE IS A URL PARAMETER PASSED TO THIS SCRIPT
    if (!$HasNourl) {
        //URL DECODE THE URL
        $url = urldecode($urlRaw);
        //CREATE AN INSTANCE OF THE UTILS CLASS TO USE ITS FUNCTIONS (startsWith, EndsWith and echoNl)
        $ut = new BPUtil($debug);
        //FIRST OCCURANCE OF CUSTOM ECHO STATEMENTS (FROM THE BPUtil class instance) WHICH WILL ONLY BE OUTPUT TO SCREEN IF QUERY STRING debug IS SET TO true
        
        //ECHO OUT THE RSS FEED URL
        $ut->echoNl("URL passed in (woohoo!):" . $url);
        //ECHO OUT THE INDEX OF THE URL WE ARE TAREGTING IN THE RSS FEED
        $ut->echoNl("The index of a link in the RSS feed: " . $indexurl);
        //ECHO OUT THE INDEX OF THE ARTICLE IMAGE IN THE RESULTING MARKUP THAT WE ARE TARGETING
        $ut->echoNl("The index of a link in the RSS feed: " . $indexthumb);

        //INSTANTIATE THE RSS RIPPER CLASS, GAINIGN ACESS TO ITS MULTIPLE FUNCTIONS, PASSING ITS CONSTRUCTYOR THE BPUtil CLASS INSTANCE
        $rr = new RSSRipper($ut);

        //LET THE SCREEN KNOW THAT WE INSTANTIATED THE RSSRippper CLASS
        $ut->echoNl("RSSRipper instance created");

        //SO THAT WE DONT HAVE TO KEEP PINGING THE RSS FEED, RISKING ITS IRE(!), CHECK SESSION FOR THE SPECIFIC URL
        if (!isset($_SESSION[$url])) {
            //WE DO NOT HAVE THE RSS FEED MARKUP IN A SESSION VARIABLE, SO RETRIEVE IT FROM THE REMOTE URL
            
            $rssMarkup = $rr->getMarkupFromUrl($url, true);// FIRST INSTANCE OF AN RSSRipper function 
            
            //STICK THE RSS FEED MARKUP INTO THE SESSION THAT WE JUST CHECKED (FOR NEXT TIME WE PING THIS SCRIPT WITHTHE SAME RSS FEED)
            $_SESSION[$url] = $rssMarkup;
        } else {
            //WE HAVE A SESSION INSTANCE VARIABLE WITH THIS RSS FEED THAT WE CAN USE WITHOUT HAVING TO RE-ASK THE REMOTE URL FOR THE MARKUP AGAIN
            $ut->echoNl("Already retrieved markup from RSS feed at " . $url . "; using that.");
            //ASSIGN THE MARKUP FROM THE SESSION VARIABLE TO OUR rssMarkup VARIABLE
            $rssMarkup = $_SESSION[$url];
        }
    
        //ECHO OUT IN DEBUG MODE THE ENTIRE RSS FEED MARKUP
        $ut->echoNl("Retrieved the following markup of the RSS feed:".$rssMarkup);
        $ut->echoNl($rssMarkup);
        
        //USE RSSRipper INSTANCE TO GET ALL URLS (REMOTGE ARTICLE URLS) FROM THE RSS MARKUP
        $hasUrlListOfString = $rr->getUrlsFromRssMarkup($rssMarkup);
        
        //ONLY CARRY OUT THE NEXT STEPS IF THE LAST TASK SUCCEEDED (ITS state VARIABLEMUST BE TRUE)
        if ($hasUrlListOfString->state) {
            $ut->echoNl("Retrieved List of urls from RSS feed markup:");
            //THE obj RETURNED FROM THE CALL TO THE getUrlsFromRssMarkup FUNCTION HAS THE ARRAY OF URLS
            $urlListOfString = $hasUrlListOfString->obj;//ASSIGN A VARIABLE TO THE ARRAY 
            //BECAUSE WE ARE GOING TOUSE THE PHP print_r FUNCTION TO ECHO OUT AN ARRAY, WE NEED TO CHECK FOR debug true HERE
            if ($debug) {
                //PRINT THE CONTENTS OF THE ARRAYT OF URLS DISCOVERED IN THE RSS MARKUP HERE
                print_r($urlListOfString);
            }
        
            // USE THE INDEX (indexurl) PASSED INTO THIS SCRIPT TO ISOLATE INTO A VARIABLE THE TARGETED URL 
            $targetedUrlInRss =$urlListOfString[$indexurl];
            //IN DEBUG MODE, ECHO OUT THIS TARGETED URL TO THE SCREEN
            $ut->echoNl("Retrieved targeted url fromn this list at index " . $indexurl . ":");
            $ut->echoNl($targetedUrlInRss);

            //INFORM SCRFEEN THAT WE ARE NOW ATTEMPTING TO FETCH THE HTML MARKUP FROM THIS URL
            $ut->echoNl("Retrieving html markup from URl:");
            //USE AN RSSRipper INSTANCE FUNCTION TO EXTRACT THE HTML FROM THIS URL
            $htmlMarkup = $rr->getMarkupFromUrl($targetedUrlInRss, false);//dont use curl
            
            //IN DEBUG MODE, ECHO OUT TO THE SCREEN THE HTML MARKUP RETRIEVED (TELL THE PAGE AT THIS POINT NOT TO BE THE DEFAULT MIME TYPE OR IT WILL RENDER HTML)
            header('Content-Type: text/plain');
            $ut->echoNl($htmlMarkup);

            //INFORM SCREEN THBAT WE ARE NOW GOING TO ATTEMPT TO EXTRACT TH EIMAGE TAG DATA FROM THE HTML MARKUP (uses regex under the hood because hosting doesn't always have Dom functions)
            $ut->echoNl("Attempting to get image tags from html markup:");
            $hasImageTagListOfString = $rr->getImageTagsArrayFromHtmlMarkup($htmlMarkup);

            //DECLARE A VARIABLE THAT WILL STIORE THE RESULT OF THIS ATTEMPT
            $imageTagListOfString = null;

            //ONLY PROCEED IF THE ATTEMPT TO GET IMAGE TAGS SUCCEEDED; DESIGNATED BYU THE state VARIABLE
            if ($hasImageTagListOfString->state) {
                //INFOIRM SCREEN THAT WE ARE GOING TO ATTEMPT TO PRINT TO THE SCREEN THE RESULT OF EXTRACTING THESE IMAGES
                $ut->echoNl("Printing image list from the html markup retrieved:");
                //ASSIGN THE imageTagListOfString TO THE RESULT 
                $imageTagListOfString = $hasImageTagListOfString->obj;
        // AGAIN, BECAUSE WE ARE USING THE BUILT IN PHP print_r FUINCTION TO ECHO THE IMAGE TAG ARRAY DATA , WE NEED TO CHECK FOR debug HERE BEFORE DOING THAT
                if ($debug) {
                    //PRINT THE ARRAY OF IMAGE TAGS TO THE SCREEN. THE FUNCTION GENERATES THIS DATA IN A SPECIFIC WAY AS WILL BE SEEN WHEN WE GET AT THE IMAGE WE WANT
                    print_r($imageTagListOfString);
                }
            } else {
                // HERE, WE WILL BE REPORTING TO THE SCREEN THAT THERE WAS AN ISSUE WITH RETRIEVING ANY IMAGE TAGS FROM THE HTML MARKUP
                $ut->echoNl("Could not retrieve image list from html markup:".$hasImageTagListOfString->obj);
                $ut->echoNl("Markup scanned for urls to use:");
                $ut->echoNl($htmlMarkup);
            }

            //CREATE A VARIABLE OF NULL TO HOLD THE INTITIAL STATE OF THE OBJKECT THAT WILL CONTAIN THE TAREGETED IMAGE URL 
            $hasTargetedImageUrl = null;
            //CREATE A VARIABLE OF EMPTY STRING TO HOLD THE INITIAL STATE OF THE TARGETED IMAGE URL
            $imageSrc = "";
            //ONLY ENTER THIS IF THERE IS AN ARRAY OF IMAGE TAGS
            if (!is_null($imageTagListOfString)) {
                //ASSIGN THE hasTargetedImageUrl VARIABLE BY GETTING THE indexthumbth (!) ITEM FROM THE ARRAY OF imageTagListOfString
                $hasTargetedImageUrl = $imageTagListOfString[$indexthumb];
                //FROM HAVING INSPECTED THE WAY THE DATA IS, GRAB THE attributes OBJECT AND THE src OBJECT OF THAT, WHICHB WILL BE THE IMAGE URL
                $attr = $hasTargetedImageUrl["attributes"];
                $imageSrc = $attr["src"];
            }
            //IN DEBUG MODE, INFORM THE PAGE OF THE INDEX OF THE IMAGE TO USE AND THE SRC DISCOBVERED AT THAT INDEX IN THE HTML MARKUP
            $ut->echoNl("Found src of image to use at index " . $indexthumb .": ".$imageSrc);
            //MAKE SURE WE HAVE AN http BY CHECKING TO SEE IF THE URL STARTS WITH JUST //, IN WHICH CASE PREFIX THE http 
            if ($ut->startsWith($imageSrc, "//")) {
                $imageSrc = "http:".$imageSrc;
            }
            //RETRIEVE THE FILE EXTENSION FROM THE IMAGE URL
            $hasFileExtension = $rr->getFileExtensionFromUrl($imageSrc);
            //ONLY ATTEMPT THE NEXT BUT IF WE HAVE A FILE EXTENSION
            if ($hasFileExtension->state) {
                //ASSIGN THE FILE EXTENSION TO A VARIABLE FROM THE RSSRipper CLASS INSTANCE FUNCTION's obj
                $fileExtension = $hasFileExtension->obj;
                //USE THAT FILE EXTENSIUON TO ASSIGN A VARFIABLE TO A PROPPER HTTP HEADER MIME TYPE
                $hasMimeType = $rr->getMimeTypeFromFileExtension($fileExtension);
                //IF WE HAVE A MIME TYPE (BY CHECKING THE SUCCESS OF ITS RETIEVAL FROM THE FILE EXTENSION VIA THE state VARIABLE)
                if ($hasMimeType->state) {
                    //THEN GET AT THE MIME TYPE VIA THE obj VARIABLE
                    $mimeType = $hasMimeType->obj;
                    /// AND MAKE THE PAGE RENDER THE IMAGE ITESELKF AS IF IT WERE AN IMAGE BY PAINGTIN ITS BYTES TO THE PAGE AND SETTING THE PAGE HEADER MIME TYPE TO THE ONE WE RETRIEVED 
                    $rr->renderImageResult($mimeType, $imageSrc);
                }
            }
        }
    }
