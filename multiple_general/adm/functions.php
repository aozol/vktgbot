<?php


/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/
require_once(dirname(__FILE__).'/../functions.php');

//require_once(dirname(__FILE__).'/../update_MySQL.php');


$sql=mysqli_query($dblink,"SELECT token,confirmToken,secret FROM `".DBP."vkApi`");
   
list($token,$confirmToken,$secret)=mysqli_fetch_array($sql);

function hash_pass($pass)
{

    return sha1(md5(md5($pass).'VkBotBotMyBot'));

}

//*
function message_queue($token,$vkId,$messageText,$dateTime,$keyboard='')
{
    GLOBAL $dblink, $DBP, $taskId;
    
    if(!$dateTime)
        $dateTime=date("Y-m-d H:i:s");
    
    if(!isset($taskId))
        $taskId='NULL';
    
    $sql=mysqli_query($dblink,"SELECT errorCode FROM `".DBP_GENERAL."message_error`  WHERE vkId={$vkId} AND token='{$token}'");
    
    if (list($errorCode)=mysqli_fetch_array($sql))
    {
        return array(FALSE,$errorCode);
    }
    
    else
    {
        $sql=mysqli_query($dblink,"SELECT COUNT(vkId) FROM `".DBP_GENERAL."message_queue` WHERE dateTime='{$dateTime}' AND vkId={$vkId} AND messageText='{$messageText}'");
        
        //bot_debugger("SELECT COUNT(vkId) FROM `".DBP_GENERAL."message_queue` WHERE dateTime='{$dateTime}' AND vkId={$vkId} AND messageText='{$messageText}'");
        
        list($n)=mysqli_fetch_array($sql);
        
        if($n==0)
        {
            mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."message_queue` (dateTime,vkId,token,messageText,keyboard,taskId) VALUES ('{$dateTime}',{$vkId},'{$token}','".mysqli_real_escape_string($dblink,$messageText)."','".mysqli_real_escape_string($dblink,$keyboard." \$DBP='{$DBP}';")."',{$taskId})");
            
            return array(TRUE,0);
        }
        
        else
            return array(FALSE,'"Попытка отправить несколько одинаковых сообщений"');
        
    }
}

//*/
?>
