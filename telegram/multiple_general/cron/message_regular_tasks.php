<?php



ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/

require_once(dirname(__FILE__).'/../adm/functions.php');

$nowDate=date('Y-m-d H:i:00');
$currentDateTime = new DateTime($nowDate);
    
for ($i = 0; $i < 5; $i++)
{    
    
    $sql=mysqli_query($dblink,"SELECT taskId, ADM_VK_ID,DBP,dataJson FROM `".DBP_GENERAL."message_regular_tasks` WHERE month IN(0,".$currentDateTime->format('m').") AND day IN(0,".$currentDateTime->format('d').") AND weekday IN(0,".($currentDateTime->format('w')+1).") AND hour=".$currentDateTime->format('H')." AND minute=".$currentDateTime->format('i')." AND isDone=0 AND lastDone != '".$currentDateTime->format('Y-m-d H:i:s')."'");


    
    while (list($taskId,$ADM_VK_ID,$DBP,$dataJson)=mysqli_fetch_array($sql))
    {
        mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."message_tasks` (dateTime,ADM_VK_ID,DBP,dataJson) VALUES ('{$currentDateTime->format('Y-m-d H:i:s')}','{$ADM_VK_ID}','{$DBP}','".mysqli_escape_string($dblink,$dataJson)."')");
        
        mysqli_query($dblink,"UPDATE `".DBP_GENERAL."message_regular_tasks` SET lastDone='".$currentDateTime->format('Y-m-d H:i:s')."' WHERE taskId={$taskId}");
        

    }
    
    $currentDateTime->modify('-10 minutes');

}



?>
