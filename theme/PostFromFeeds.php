<?php /* Template Name: Post From Feed */ ?>
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
?>