<?php
/**
 *Creates a file for each book and topic and dumps it into the filesystem
 * also creates a json file indexed by folder with all the contents in there.
 */


$file = file_get_contents('ICT_R2.0.xml');

preg_match_all("'<Book>(.*?)</Book>'si", $file, $match);

$folderObjects=array();

//Books
foreach ($match[1] as $k => $v) {
    $fxml = new SimpleXMLElement('<Book>' . $v . '</Book>');
    $id = (string)$fxml->Object[0]->ID;
    $description = (string)$fxml->Object[0]->Description;
    $folderid = (string)$fxml->Object[0]->FolderID;
    $basedOn = (string) $fxml->Object[0]->BasedOn;
    //echo $id.' '.$folderid.'<br />';
    if(!isset($folderObjects[$folderid])){$folderObjects[$folderid]=array();}
    $folderObjects[$folderid][]=array('id'=>$id,'description'=>$description, 'basedOn'=>$basedOn);
    file_put_contents('./objects/'.$id.'.xml','<Book>'.$v.'</Book>');
}

preg_match_all("'<Topic>(.*?)</Topic>'si", $file, $match);
//Topics
foreach ($match[1] as $k => $v) {
    $fxml = new SimpleXMLElement('<Topic>' . $v . '</Topic>');
    $id = (string)$fxml->Object[0]->ID;
    $description = (string)$fxml->Object[0]->Description;
    $folderid= (string)($fxml)->Object[0]->FolderID;
    $basedOn = (string) $fxml->Object[0]->BasedOn;
    //echo $id.' '.$folderid.'<br />';
    file_put_contents('./objects/'.$id.'.xml','<Topic>'.$v.'</Topic>');
    if(!isset($folderObjects[$folderid])){$folderObjects[$folderid]=array();}
    $folderObjects[$folderid][]=array('id'=>$id,'description'=>$description, 'basedOn'=>$basedOn);
}

file_put_contents('./folderObjects.json',json_encode($folderObjects));

