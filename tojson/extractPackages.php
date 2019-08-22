<?php
/**
 * Created by PhpStorm.
 * User: laurencesellars
 * Date: 2019-08-22
 * Time: 17:23
 *
 * Extracts packages. requires folderObjects.json which comes from createObjects.php
 *
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$folderObjects=json_decode(file_get_contents('./folderObjects.json'),true);

$units=array();


function createSeries($pc){
    $out=array();
    foreach($pc as $pck=>$pcv){
      $pc2=explode('-',trim($pcv));
      if(count($pc2)>1){
          $pci=$pc2[0];
          while($pci < $pc2[1]){
              $out[]=(string)$pci;
              $pci=(float)$pci+0.1;
          }
          $out[]=(string)$pci;//last one after loop finishes
      }
      else{
          $out[]=$pcv;
      }
    }
    return $out;
}

foreach($folderObjects as $k => $v){
    echo '<br />Folder:'.$k;
    $folder=array();
    foreach($v as $k1 => $v1){
        if($v1['basedOn']=='513640'){//It's a unit package
            echo '<ul>';
            $unit=array();
            echo 'id:'.$v1['id'].' '.$v1['description'];
            $unit['code']=explode(' ',$v1['description']);$unit['code']=$unit['code'][0];
            $pack=file_get_contents('./objects/'.$v1['id'].'.xml');
            $x= new SimpleXMLElement($pack);
            foreach($x->VariableAssignments->children() as $vav=>$kav){
                if($kav->Name=='Title') {
                    $unit['name']=(string) $kav->Value;
                }
            }
            foreach($x->ContentsNodes->children() as $k2=>$v2){
                echo '<li>';
               $node2=(string) $v2['id']; echo ' Node='.$node2. ' ' . (string) $x->Object->Description;
               $pack2=file_get_contents('./objects/'. $node2.'.xml');
               $x2= new SimpleXMLElement($pack2);
               //var_dump($x2->Object->BasedOn);
                echo '<ul>';
                foreach($x2->ContentsNodes->children() as $k3=>$v3){
                    echo '<li>';
                    $node3=(string) $v3['id']; echo ' Node='.$node3;
                    $pack3=file_get_contents('./objects/'. $node3.'.xml');
                    $x3 = new SimpleXMLElement($pack3);
                    echo  ' ' . (string) $x3->Object->Description;

                    switch ((string) $x3->Object->BasedOn){
                        case "475":
                            //echo "Modification History";
                            //this has an issue because it comes in the unit and assessment conditions
                            break;
                        case "513615":
                           // echo "Application";
                            //$unit['application']=(string) $x3->Text;
                            break;
                        case "513634":
                            echo "Unit Sector";
                            //$unit['sector']=(string) $x3->Text;
                            break;
                        case "513620":
                            //echo "Elements and Performance Criteria";
                            $trLoop=0;
                            $unit["elements"]=array();
                            $unit["pc"]=array();
                            foreach($x3->Text->table->children() as $trk => $trv)
                                {
                                    if($trLoop>1) {
                                        $element=explode('. ',strip_tags($trv->td[0]->asXML()),2);
                                        $trpc=array();
                                        foreach($trv->td[1]->children() as $tdr => $tdv)
                                            {
                                            $pc=explode(' ',strip_tags((string) $tdv),2);
                                            $trpc[]=$pc[0];
                                            $unit["pc"][$pc[0]]=array("text"=>$pc[1]);
                                            }
                                        $unit["elements"][]=array('id'=>(string) $element[0], 'name'=>$element[1],'pc'=>$trpc);
                                        //echo $trv->td[1]->asXML()
                                    }
                                $trLoop++;
                                }
                        case "513622":
                            //echo "Foundation Skills";
                            $unit["foundationSkills"]=array();
                            $trLoop=0;
                            foreach($x3->Text->table->children() as $trk => $trv){
                                if($trLoop>1) {
                                    $fs=array();
                                    $fs["name"]=strip_tags($trv->td[0]->asXML());
                                    $pc=explode(',',str_replace(' ','',strip_tags($trv->td[1]->asXML())));
                                    $fs['pc']=array();

                                    //remove dashes from performance criteria, replace with series.
                                    //$fs['pc']=createSeries($pc);
                                    //var_dump(createSeries($pc));
                                    $fs['pc']=createSeries($pc);
                                    $fs["description"]=array();
                                    if(!is_null($trv->td[2])){
                                        $fsdCount=0;
                                        foreach($trv->td[2]->children() as $fsdk => $fsdv)
                                            {
                                                $fs["description"][] = array("line" => $fsdCount, "text" => strip_tags($fsdv->asXML()));
                                            }
                                        //$fs["description"]=strip_tags($trv->td[2]->asXML());
                                    }
                                    $unit["foundationSkills"][]=$fs;
                                }
                                $trLoop++;
                            }
                            break;
                        case "513626":
                            //echo "Unit Mapping Information";
                            $unit['mapping']=(string) $x3->Text;
                            break;
                        case "513625":
                            //echo "Links";
                            //This has an issue because it comes in both the unit and assessment conditions pathways
                            break;
                        case "513629":
                            //echo "Performance Evidence";
                            //$unit['PE']=(string) $x3->Text->asXML();
                            $pe=array();
                            foreach($x3->Text->children() as $pek => $pev)
                                {
                                if($pev["id"]=='13'){
                                    $pe[]=array('text'=>strip_tags($pev->asXML()),"list"=>array());
                                    }
                                if($pev["id"]=='14'){
                                    end($pe);
                                    $key=key($pe);
                                    $pe[$key]['list'][]=strip_tags($pev->asXML());
                                    }
                                }
                            $unit['performanceEvidence']=$pe;
                            break;
                        case "513623":
                            //echo "Knowledge Evidence";
                            //$unit['PE']=(string) $x3->Text->asXML();
                            $ke=array();
                            foreach($x3->Text->children() as $kek => $kev)
                            {
                                if($kev["id"]=='13'){
                                    $mine=array();
                                    $ke[]=array("text"=>strip_tags($kev->asXML()),"list"=>$mine);
                                    //echo ' '.$kev.'<br />';
                                }
                                if($kev["id"]=='14'){
                                    end($ke);
                                    $key=key($ke);
                                    //echo ' '.$kev.' ';
                                      $conttt=strip_tags($kev->asXML());
                                    if(isset($ke)&&isset($ke[$key])) {
                                        $ke[$key]['list'][] = $conttt;
                                    }
                                }
                            }
                            $unit['knowledgeEvidence']=$ke;
                            break;
                        case "513616":
                            //echo "Assessment Conditions";
                            //$unit['AC']=(string) $x3->Text;
                            break;
                        default:
                        //echo (string) $x3->Object->BasedOn.": This template is an unknown Document Type";

                    }
                    echo '</li>';
                }
                echo '</ul>';
               echo '</li>';
            }
            //var_dump($unit["performanceEvidence"]);
            echo '</ul>';
           file_put_contents('./units/'.$unit['code'].'.unit.json',json_encode($unit));
        }
    }
}