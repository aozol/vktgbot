<?php


/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/

require_once(dirname(__FILE__).'/../adm/functions.php');

$nowDate=date('Y-m-d H:i:00');

$sql=mysqli_query($dblink,"SELECT ADM_VK_ID,DBP,dataJson FROM `".DBP_GENERAL."message_regular_tasks` WHERE month IN(0,".date('m').") AND day IN(0,".date('d').") AND weekday IN(0,".(date('w')).") AND hour=".date('H')." AND minute=".date('i')." AND isDone=0");

$isTask=0;
  
while (list($ADM_VK_ID,$DBP,$dataJson)=mysqli_fetch_array($sql))
{
    mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."message_tasks` (dateTime,ADM_VK_ID,DBP,dataJson) VALUES ('$nowDate','{$ADM_VK_ID}','{$DBP}','".mysqli_escape_string($dblink,$dataJson)."')");
    $isTask=1;
}

if($isTask)
{
    file_get_contents('http://vkbot.aozol.ru/multiple_general/cron/message_tasks.php');
    //file_get_contents('http://vkbot.aozol.ru/multiple_general/adm/message_sender.php');
}

//echo file_get_contents('http://vkbot.clubevrika.ru/multiple_general/adm/message_sender.php?from_tasks=1');



?>
