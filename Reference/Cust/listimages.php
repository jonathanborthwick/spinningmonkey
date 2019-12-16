<?php
$html = file_get_contents('http://www.website.any');
preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i',$html, $matches ); 
echo $matches[ 1 ][ 0 ];
?>