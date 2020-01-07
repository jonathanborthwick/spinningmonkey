<?php namespace brog;

/**
    Look through all posts to see which ones use an rss feed as their basis.
    How we determine that could be to look at each post's category for one called called rss.
    We grab the markup of each posts' rss feed and get the markup of each of the hyperlink descriptions and any thumbnail.
    If we have this string up to a certain degree of similarity  make it probablistic or something or just use the string exactly, skip it.
    if it is unique, create a draugh post out of it. Give the new post a category of auto.
    We need a database that records the article content that we have already done along side the post id that it is in - design on excel.
    Initially, all I think i need for this is a uniquer id for the record, the wordpress post id and the string content  from the rss feed - not what the gewnerated post has. important.
    Also initially, we dont need to have the theme or a plugin generate the database table. We can use phpmyadmin for that. once the functionality is in place, we can make it a plugin or theme process.
Eg.
    CREATE TABLE `spinnis0_WPLXP`.`LXP_Feed_To_Post` ( `POST_ID` INT NOT NULL , `LINK_DESCRIPTION` TEXT NOT NULL , `USER_ID` INT NULL , `ENTER_ON_DATE` DATE NULL ) ENGINE = MyISAM;
*/

//Get list of all posts
//get ones with categoiry rss
//get markup of each feed
  //for the markup, get string content of description
  //check to see if we have this struing content already
  //if we have not, write the string content to the database for next time
  //and create a new draft post with this new article summary and with category auto

class FPObj{
    public $Index = 0;
    public $ID = 0;
    public $Feed = "";
}

class FeedToPost
{
    public function GetPostContent($postsArray)
    {
        $ret = [];
        $index = 0;
        foreach ($postsArray as $obj) {
            $content = $obj->post_content;
            $id = $obj->ID;
            //strip out feedzy stuff
            $content  = str_replace("[","",$content);
            $content  = str_replace("]","",$content);
            $splitForFeed = explode("feeds=\"",$content);
            $justFeed1 = $splitForFeed[1];
            $splitForlastQuote = explode("\"",$justFeed1);
            $justFeed = $splitForlastQuote[0];
            $no = new FPObj();
            $no->ID = $id;
            $no->Feed = $justFeed;
            $no->Index = $index;
            array_push($ret,$no);
            $index++;
        }
        return $ret;
    }

    public function ToString()
    {
        return "Class FeedToPost";
    }
}

?>

