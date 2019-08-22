<?php

$pc=array('1.2-1.4', '2.2- 2.7', '3.1-3.4');
function createSeries($pc){
    $out=array();
    foreach($pc as $pck=>$pcv){
        $pc2=explode('-',trim($pcv));
        if(count($pc2)>1){
            $pci=$pc2[0];
            while($pci < $pc2[1]){
                $out[]=(string)$pci;
                $pci=$pci+(0.1);
            }
            $out[]=(string)$pci;//last one after loop finishes
        }
        else{
            $out[]=$pcv;
        }
    }
    return $out;
}
var_dump(createSeries($pc));