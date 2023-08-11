<?php
require_once('../conf.php');

require_once(GENERAL_DIR.'adm/functions.php');
require_once(GENERAL_DIR.'adm/login.php');


require_once('../functions.php');

$sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE isActive=1");
   
list($startDate)=mysqli_fetch_array($sql);

$sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE startDate<'{$startDate}' ORDER BY startDate DESC");
   
list($startId)=mysqli_fetch_array($sql);
list($startId)=mysqli_fetch_array($sql);


$sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");
   
list($startText)=mysqli_fetch_array($sql);

$replyText='Лучшие шутки прошлого голосования:';



$place=array();

$sql=mysqli_query($dblink,"SELECT finishId,finishText FROM `".DBP."finishes` WHERE startId={$startId} ORDER BY avgVote DESC");

$n=1;

while ($n<=3)
{
    
    
    
    list($finishId,$fT)=mysqli_fetch_array($sql);
    $finishText[$finishId]=$fT;
    $place[$finishId]=$n;
    $n++;
}

$sql=mysqli_query($dblink,"SELECT finishId,finishText FROM `".DBP."finishes` WHERE startId={$startId} ORDER BY medVote DESC");

$n=1;

while ($n<=3)
{
    
    list($finishId,$fT)=mysqli_fetch_array($sql);
    $finishText[$finishId]=$fT;
    
    if(isset($place[$finishId]))
        $place[$finishId]+=$n;
    else
        $place[$finishId]=$n;
    $n++;
}

asort($place);


foreach ($place as $finishId=>$p)
   $replyText.='
'.str_replace('...',' ',$startText." {$finishText[$finishId]}");

echo $replyText;
 
//*/
require_once(GENERAL_DIR.'adm/template/bottom.php');
?>
