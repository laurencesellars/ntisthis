<?php
/**
 * Created by PhpStorm.
 * User: laurencesellars
 * Date: 2019-08-21
 * Time: 23:30
 */
$files = scandir('./books');
foreach($files as $file) {
    $f=file_get_contents('./books/'.$file);
    preg_match_all("'<description>(.*?)</description>'si", $f, $match);
    if($match) echo $file.' '.$match[1].'<br />';
}