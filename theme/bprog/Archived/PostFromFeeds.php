<?php namespace brog;/* Template Name: Post From Feed */ 
   require_once('FeedToPost.php');
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
 <style>
   .hide{
       display:none;
   }
</style>

<?php

the_post();
the_content();
//echo out what the page is doing
$fp = new FeedToPost();
//echo($fp->ToString());//test

//get array of all posts with category RSS
$posts = query_posts( array( 'category_name'  => 'rss' ) );
$content = $fp->GetPostContent($posts);
echo("<h2>Posts</h2>");
?>
<textarea rows="40" cols="120">
<?php
   print_r($content);
?>
   posts array:
<?php
   print_r($posts);
?>
</textarea>