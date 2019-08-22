<?php
/**
 * Use after createObjects.php
 * Searches through folders in xml file and lists its contents.
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$folderObjects=json_decode(file_get_contents('./folderObjects.json'),true);
//if(!file_exists('z.z')) {
$file = file_get_contents('ICT_R2.0.xml');

preg_match_all("'<folder>(.*?)</folder>'si", $file, $match);


$tree = array();
$names = array();

foreach ($match[1] as $k => $val) {
    $fxml = new SimpleXMLElement('<a>' . $val . '</a>');
    $id = (string)$fxml->ID;
    $parentId = (string)$fxml->ParentFolderID[0];
    $name = (string)$fxml->Name;
    //var_dump($parentId);
    //echo $parentId.'*';
    if (!isset($tree[$parentId])) {
        $tree[$parentId] = array();
        }
    $tree[$parentId][] = $id;
    $names[$id] = $name;
    }

function outputChildren($tree,$names,$index,$count,$folderObjects){
if(isset($names[$index]))
    {
    echo '<li>'.$index.'-'.$names[$index];
    if(isset($folderObjects[$index])){
        //var_dump($folderObjects[$index]);
        foreach($folderObjects[$index] as $k => $v) {
            //var_dump($folderObjects[$index][$k]);
            //var_dump($folderObjects[$index]);
            echo '<br />'.$folderObjects[$index][$k]['id'].':'.$folderObjects[$index][$k]['description'];
        }
    echo '</li>';
    }
}
echo '<ul>';
    if(isset($tree[$index])) {
        foreach ($tree[$index] as $k => $v) {
        //echo $v . '*';
        echo outputChildren($tree, $names, $v, $count++,$folderObjects);
        }
    }
    echo '</ul>';
}
echo '<ul>';
    outputChildren($tree,$names,0,0,$folderObjects);
    echo '</ul>';
//}



echo 'fin';