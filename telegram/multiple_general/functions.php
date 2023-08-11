<?php


//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

ini_set('max_execution_time',25);

require_once(dirname(__FILE__).'/conf.php');

require_once(dirname(__FILE__).'/../vendor/autoload.php');

require_once(dirname(__FILE__).'/API/VK_TG.php');

$dblink=mysqli_connect(DB_SERVER, DB_LOGIN, DB_PASS);
  if (!$dblink) { echo 'Ошибка подключения к базе'; exit; }
  mysqli_query($dblink,"SET NAMES 'utf8mb4'"); //на всякий случай, бывают проблемы с русскими буквами
  mysqli_select_db($dblink,DB_NAME);
  
require_once(dirname(__FILE__).'/update_MySQL.php');  

function log_msg($message) {
  if (is_array($message)) {
    $message = json_encode($message);
  }
  _log_write('[INFO] ' . $message);
}
function log_error($message) {
  if (is_array($message)) {
    $message = json_encode($message);
  }
  _log_write('[ERROR] ' . $message);
}
function _log_write($message) {
  $trace = debug_backtrace();
  $function_name = isset($trace[2]) ? $trace[2]['function'] : '-';
  $mark = date("H:i:s") . ' [' . $function_name . ']';
  $log_name = BOT_LOGS_DIRECTORY.'/log_' . date("j.n.Y") . '.txt';
  file_put_contents($log_name, $mark . " : " . $message . "\n", FILE_APPEND);
}




function R_strlow ($str){
if (mb_strtolower ("Ж")!="ж") $str = strtr(
$str,
'ЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ',
'йцукенгшщзхъфывапролджэячсмитьбю');
else $str=mb_strtolower($str);
return $str;
};

function R_strup ($str){
if (mb_strtoupper ("ж")!="Ж") $str = strtr(
$str,
'йцукенгшщзхъфывапролджэячсмитьбю',
'ЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ');
else $str=mb_strtoupper($str);
return $str;
}




function user_auth($vkId,$gId=0)
{
  GLOBAL $dblink,$currentGroupId;
  
  if(!$gId)
     $gId=$currentGroupId;

  $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."db` WHERE vkId={$vkId} AND vkGroupId={$gId}");
  //vkApi_messagesSend(ADM_VK_ID, "SELECT vkId FROM `".DBP."db` WHERE vkId={$vkId} AND vkGroupId={$gId}");
  
  if(!mysqli_num_rows($sql))
  {
    
    $newUser=1;
    user_mlist_manage($vkId,0,2,$gId); //подписываем на рассылку
    
    return 1;
  }
  
  else
    return 0;
  
}

function user_mlist_check($vkId,$mlistId,$gId)
{
    GLOBAL $dblink;
    
    if(defined('DBP'))
        $DBP=DBP;
    else
        $DBP=$GLOBALS['DBP'];
    
    if($gId)
        $sql=mysqli_query($dblink,"SELECT vkId FROM `".$DBP."db` WHERE vkId={$vkId} AND mlistId={$mlistId}  AND vkGroupId={$gId} AND unsub=0");
    else
        $sql=mysqli_query($dblink,"SELECT vkId FROM `".$DBP."db` WHERE vkId={$vkId} AND mlistId={$mlistId} AND unsub=0"); 


    if (mysqli_num_rows($sql))
        return TRUE;
    else
        return FALSE;
}

// подписка/отписка от рассылок
function user_mlist_manage($vkId,$mlistId,$action=1,$gId=0)
{
  GLOBAL $dblink,$currentGroupId;
  
    if(defined('DBP'))
        $DBP=DBP;
    else
        $DBP=$GLOBALS['DBP'];
  
  if(!$gId)
     $gId=$currentGroupId;
  
  switch($action)
  {
    
    case 2: //подписка на рассылку по умолчанию для текущей группы
        
        
        $sql=mysqli_query($dblink,"SELECT mlistId FROM `".$DBP."mlists` WHERE isDefault=1 AND vkGroupId IN ({$gId},0) ORDER BY vkGroupId DESC");
        
        if(!mysqli_num_rows($sql))
            bot_debugger("Ошибка бота: список по умолчанию не опеределен. Укажите один из списков рассылки как список по умолчанию, чтобы ошибка не повторялась.");

        while (list($mlistId)=mysqli_fetch_array($sql))
        {
           mysqli_query($dblink,"INSERT INTO `".$DBP."db` (vkId,mlistId,vkGroupId) VALUES ('{$vkId}','{$mlistId}','{$gId}')");
           //vkApi_messagesSend(ADM_VK_ID, "INSERT INTO `".DBP."db` (vkId,mlistId,vkGroupId) VALUES ('{$vkId}','{$mlistId}','{$gId}')");
        }
        $affectedRowsN=mysqli_affected_rows($dblink);
                
                
    break;
    
    case 1:
        mysqli_query($dblink,"INSERT INTO `".$DBP."db` (vkId,mlistId,vkGroupId) VALUES ('{$vkId}','{$mlistId}','{$gId}')");
        $affectedRowsN=mysqli_affected_rows($dblink);
        
        mysqli_query($dblink,"UPDATE `".$DBP."db` SET unsub=0 WHERE vkId={$vkId} AND mlistId={$mlistId}");
                 
    break;
    
    case 0:
        if (is_array($mlistId))
           $mlist_str=implode(',',$mlistId);
        else
           $mlist_str=$mlistId;
        mysqli_query($dblink,"UPDATE `".$DBP."db` SET unsub=1 WHERE vkId={$vkId} AND mlistId IN ({$mlist_str}) AND vkGroupId={$gId}");
        
        //bot_debugger("UPDATE `".DBP."db` SET unsub=1 WHERE vkId={$vkId} AND mlistId IN ({$mlist_str}) AND vkGroupId={$gId}");
        
        $affectedRowsN=mysqli_affected_rows($dblink);
      
    break;
  }
  
   if ($affectedRowsN>0)
      return TRUE;
   else
      return FALSE;
  
}

function user_eventreg_manage($vkId,$eventId,$roleId,$action=1,$gId=0)
{
  GLOBAL $dblink,$currentGroupId;
  
    if(defined('DBP'))
        $DBP=DBP;
    else
        $DBP=$GLOBALS['DBP'];
  
  if(!$gId)
     $gId=$currentGroupId;

  switch($action)
  {
    
    case 1:
        mysqli_query($dblink,"INSERT INTO `".$DBP."events_reg_db` (vkId,eventId,roleId,vkGroupId) VALUES ({$vkId},{$eventId},{$roleId},{$gId})");
        $affectedRowsN=mysqli_affected_rows($dblink);
        
        mysqli_query($dblink,"UPDATE `".$DBP."events_reg_db` SET unsub=0 WHERE vkId={$vkId} AND eventId={$eventId} AND roleId={$roleId}");
                 
    break;
    
    case 0:
        if (is_array($roleId))
           $role_str=implode(',',$roleId);
        else
           $role_str=$roleId;
        
      mysqli_query($dblink,"UPDATE `".$DBP."events_reg_db` SET unsub=1 WHERE vkId={$vkId} AND roleId IN ({$role_str}) AND vkGroupId={$gId} AND eventId={$eventId}");
      

        
        $affectedRowsN=mysqli_affected_rows($dblink);
      
    break;
  }
  

  
   if ($affectedRowsN>0)
      return TRUE;
   else
      return FALSE;
  
}

function get_vk_ids($vkId_str,$type='array')
{
   
   if (stristr($vkId_str,'vk.com'))
   {
   
        $str=str_replace('
','',$str);
        $str=preg_replace('#[[:space:]]+#',' ',$vkId_str);
        $str=str_replace('> <','><',$str);
        $str=str_replace(',','',$str);
        $str=str_replace('|','',$str);
        //$uid_str=preg_replace('#.*<tbody>#','',$str);
        $uid_str=preg_replace('#show_more_link.*#','',$str);
        $uid_str=preg_replace('#<div class="labeled name"><a( exuser="true")* href="/(.*?)".*?#','||$2|-|',$uid_str);
        $uid_str=preg_replace('#.*?\|\|(.*?)\|-\|.*?#','$1,',$uid_str);
        $uid_str=preg_replace('#(((.*?),)+).*#','$1,',$uid_str);
        $uid_str=str_replace(',,','',$uid_str);
        $uid_str=str_replace('http://vk.com/',',',$uid_str);
        $uid_str=str_replace('https://vk.com/',',',$uid_str);
	$uid_str=str_replace('vk.com/',',',$uid_str);
        $uid_str=preg_replace('#id([0-9]+),#','$1,',$uid_str);
        $uid_str=preg_replace('#,id([0-9]+)#',',$1',$uid_str);
        $uid_str=str_replace(' ,',',',$uid_str);
        $vkId_str=$uid_str;
   }
   
   else
        $vkId_str=str_replace('
',',',$vkId_str);
   
   
   $vkIds=explode(',',$vkId_str);
   
   
   
   switch ($type)
   {
      case 'array':
         return $vkIds;
      break;
      
      case 'string':
         return implode(',',$vkIds);
      break;
   }
}

function get_vkGroup_ids($vkId_str,$type='array')
{
   
   if (stristr($vkId_str,'vk.com'))
   {
   
        $str=str_replace('
','',$vkId_str);
        $str=preg_replace('#[[:space:]]+#',' ',$str);
        $str=str_replace('> <','><',$str);
        $str=str_replace(',','',$str);
        $str=str_replace('|','',$str);
        //$uid_str=preg_replace('#.*<tbody>#','',$str);
        $uid_str=preg_replace('#show_more_link.*#','',$str);
        $uid_str=preg_replace('#<div class="labeled name"><a( exuser="true")* href="/(.*?)".*?#','||$2|-|',$uid_str);
        $uid_str=preg_replace('#.*?\|\|(.*?)\|-\|.*?#','$1,',$uid_str);
        $uid_str=preg_replace('#(((.*?),)+).*#','$1,',$uid_str);
        $uid_str=str_replace(',,','',$uid_str);
        $uid_str=str_replace('http://vk.com/',',',$uid_str);
        $uid_str=str_replace('https://vk.com/',',',$uid_str);
	$uid_str=str_replace('vk.com/',',',$uid_str);
        $uid_str=preg_replace('#id([0-9]+),#','$1,',$uid_str);
        $uid_str=preg_replace('#,id([0-9]+)#',',$1',$uid_str);
        
        $uid_str=preg_replace('#public([0-9]+),#','$1,',$uid_str);
        $uid_str=preg_replace('#,public([0-9]+)#',',$1',$uid_str);
        $uid_str=preg_replace('#group([0-9]+),#','$1,',$uid_str);
        $uid_str=preg_replace('#,group([0-9]+)#',',$1',$uid_str);
        $uid_str=preg_replace('#event([0-9]+),#','$1,',$uid_str);
        $uid_str=preg_replace('#,event([0-9]+)#',',$1',$uid_str);
        $uid_str=str_replace(' ,',',',$uid_str);
        $vkId_str=$uid_str;
   }
   
   else
        $vkId_str=str_replace('
',',',$vkId_str);
   
   $users=_vkApi_call('groups.getById', array('group_ids'=>$vkId_str));
   
   $vkIds=array();
   
   foreach ($users as $u)
      $vkIds[]=$u['id'];
   
   switch ($type)
   {
      case 'array':
         return $vkIds;
      break;
      
      case 'string':
         return implode(',',$vkIds);
      break;
   }
}

/*
function full_name($name,$sex=0)
{

   return file_get_contents('http://intservis.ru/vk_auto/full_name_api.php?name='.urlencode($name).'&sex='.$sex);
   
}*/

require_once(dirname(__FILE__).'/API/full_name/full_name.php');

function personal_text($vkIds_input,$text)
{

    GLOBAL $vkGroupId;
    
    if(is_array($vkIds_input))
        $vkIds=implode(',',$vkIds_input);
    else
        $vkIds=$vkIds_input;

   
   // время суток в начале предложения
   $t_hi[0]='Доброй ночи';
   $t_hi[1]='Доброе утро';
   $t_hi[2]='Добрый день';
   $t_hi[3]='Добрый вечер';
   
   $from[0]='%t_hi%';
   $to[0]=$t_hi[floor(date('H')/6)];

   // время суток в середине/конце предложения
   $t_hi_small[0]='доброй ночи';
   $t_hi_small[1]='доброе утро';
   $t_hi_small[2]='добрый день';
   $t_hi_small[3]='добрый вечер';
   
   $from[1]='%t_hi_small%';
   $to[1]=$t_hi_small[floor(date('H')/6)];
   
   $from[2]='%name%';
   $from[3]='%last_name%';
   $from[4]='%full_name%';
   
   $from[5]='%vkId%';
   $from[6]='%vkGroupId%';
   
   $u=userInfo($vkIds);
   
   $t=array();
   //print_r($u);

   foreach ($u as $vkUser)
   {
   
  
        $to[2]=$vkUser['first_name'];
        $to[3]=$vkUser['last_name'];
        $to[4]=$vkUser['full_name'];
        
        $to[5]=$vkUser['vkId'];
        $to[6]=$vkGroupId[$vkUser['vkId']];
        
        
        
        if($vkUser['sex'])
            $user_sex=$vkUser['sex'];
        else
            $user_sex=2; //по умолчанию берем мужской пол
        
        //шаблоны с учетом пола
        $t[$vkUser['vkId']]=preg_replace('#{(.*?)\|(.*?)}#','$'.$user_sex,$text);

        $t[$vkUser['vkId']]=str_replace($from,$to,$t[$vkUser['vkId']]);
    
    }
    
    //log_msg($t);
    
    return $t;
   
}

function userInfo($vkIds)
{
    GLOBAL $dblink;
    
    
    $sql=mysqli_query($dblink,"SELECT vkId,sex,first_name,last_name,full_name FROM `".DBP_GENERAL."userInfo` WHERE vkId IN ($vkIds)");

    $vkIds_local=array();
    $i=0;
    while ($userInfo[$i]=mysqli_fetch_assoc($sql))
    {
        $vkIds_local[$i]=$userInfo[$i]['vkId'];
        $i++;
    }
    

    
    $vkIds_arr=array_diff(explode(',',$vkIds),$vkIds_local);
    
    
    
    if(sizeof($vkIds_arr)>0)
    {
   
        $vkIds_str=implode(',',$vkIds_arr);
        
        $vkInfo=vkApi_usersGet($vkIds_str,'sex');
        
        //print_r($vkInfo);
        
        foreach ($vkInfo as $vkUserInfo)
        {
            $vkUserInfo['full_name']=full_name($vkUserInfo['first_name'],$vkUserInfo['sex']);
            $userInfo[$i]['vkId']=$vkUserInfo['id'];
            $userInfo[$i]['sex']=$vkUserInfo['sex'];
            $userInfo[$i]['first_name']=$vkUserInfo['first_name'];
            $userInfo[$i]['last_name']=$vkUserInfo['last_name'];
            $userInfo[$i]['full_name']=$vkUserInfo['full_name'];
            $i++;
            
            mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."userInfo` (vkId,sex,first_name,last_name,full_name) VALUES ({$vkUserInfo['id']},{$vkUserInfo['sex']},'{$vkUserInfo['first_name']}','{$vkUserInfo['last_name']}','{$vkUserInfo['full_name']}')");
        }
    
    }
     
    return $userInfo;

}

function log_keyboard($vkIds,$buttons)
{
    GLOBAL $dblink,$ADM_VK_ID;
    $ADM_VK_ID=2204686;
    
    if(defined('DBP'))
        $DBP=DBP;
    else
        $DBP=$GLOBALS['DBP'];
    
    $n=sizeof($buttons);
    mysqli_query($dblink,"DELETE FROM `".$DBP."keyboard` WHERE vkId IN ({$vkIds})");
    
    if (substr_count ($vkIds, ','))
        $vkIds_arr=explode(',',$vkIds);
    else    
        $vkIds_arr[]=$vkIds;
    
    //bot_debugger("DELETE FROM `".DBP."keyboard` WHERE vkId IN ({$vkIds})");
    
    foreach($vkIds_arr as $vkId)
    {
    //bot_debugger($vkId);
    
        for ($i=0;$i<$n;$i++)
            for($j=0;$j<sizeof($buttons[$i]);$j++)
            {
                mysqli_query($dblink,"INSERT INTO `".$DBP."keyboard` (vkId,buttonText,payloadText) VALUES ({$vkId},'{$buttons[$i][$j][1]}','{$buttons[$i][$j][0]['payload']}')");
                
            //bot_debugger("INSERT INTO `".$DBP."keyboard` (vkId,buttonText,payloadText) VALUES ({$vkId},'{$buttons[$i][$j][1]}','{$buttons[$i][$j][0]['payload']}')");
            }
    }
}

// функции работы с мероприятиями

function events_get($listType='plane',$buttonsType='name',$buttons_n=2)
{
    GLOBAL $dblink,$currentGroupId;
    
    if(defined('DBP'))
        $DBP=DBP;
    else
        $DBP=$GLOBALS['DBP'];
    
    $nowDate=date('Y-m-d');
    
    switch($listType)
    {
        case 'date':
            $listDate=TRUE;
            $listTime=FALSE;
        break;
        
        case 'datetime':
            $listDate=TRUE;
            $listTime=TRUE;
        break;
        
        default:
            $listDate=FALSE;
            $listTime=FALSE;
        break;
    }
    
    switch($buttonsType)
    {
        case 'namedate':
            $buttonsName=TRUE;
            $buttonsDate=TRUE;
            $buttonsTime=FALSE;
        break;
        
        case 'namedatetime':
            $buttonsName=TRUE;
            $buttonsDate=TRUE;
            $buttonsTime=FALSE;
        break;  
        
        case 'datetime':
            $buttonsName=FALSE;
            $buttonsDate=TRUE;
            $buttonsTime=FALSE;
        break;
        
        case 'date':
            $buttonsName=FALSE;
            $buttonsDate=TRUE;
            $buttonsTime=FALSE;
        break;
        
        default:
            $buttonsName=TRUE;
            $buttonsDate=FALSE;
            $buttonsTime=FALSE;
        break;
    }
    
    $sql=mysqli_query($dblink,"SELECT eventId,eventName,eventStart,eventStartTime,eventFinish,eventFinishTime,eventPreregStart,eventRegStart FROM `".DBP."events` WHERE eventPreregStart<='{$nowDate}' AND eventFinish>='{$nowDate}' AND vkGroupId IN (0,{$currentGroupId})");
    
    $events_str='';
    $events_buttons=array();
    
    $s=0;
    
    while (list($eventId,$eventName,$eventStart,$eventStartTime,$eventFinish,$eventFinishTime,$eventPreregStart)=mysqli_fetch_array($sql))
    {
        if ($listDate)
        {
            $eventInfo=' (';
            
            if ($listTime)
                $eventInfo.=date('d.m в H:i',strtotime($eventStart.' '.$eventStartTime));
            else
                $eventInfo.=date('d.m',strtotime($eventStart.' '.$eventStartTime));
            
            $eventInfo.=')';
        }
        
        else
            $eventInfo='';
        
        if ($buttonsName)
            $buttonsInfo=$eventName;
        else
            $buttonsInfo='Регистрация на';
        
        if ($buttonsDate)
        {
            
            if ($buttonsTime)
                $buttonsInfo.=date(' d.m в H:i',strtotime($eventStart.' '.$eventStartTime));
            else
                $buttonsInfo.=date(' d.m',strtotime($eventStart.' '.$eventStartTime));

        }
        
        $events_buttons[$s/$buttons_n][$s%$buttons_n][0]["payload"]="events_info:{$eventId}";
        $events_buttons[$s/$buttons_n][$s%$buttons_n][1]=$buttonsInfo;
        $events_buttons[$s/$buttons_n][$s%$buttons_n][2]='positive';
        $s++;
        
        $events_str.="
    {$s}. {$eventName}{$eventInfo}";
    }
    
    return array($events_str,$events_buttons);
}

// сервисные функции

function bot_debugger($message)
{
    
    if(defined('ADM_TG_ID'))
        $ADM_VK_ID=ADM_TG_ID;
    else
        $ADM_VK_ID=$GLOBALS['ADM_TG_ID'];
    
    //echo 'debug send to '.$ADM_VK_ID;
    
    vkApi_messagesSend($ADM_VK_ID, $message);
}

function bot_support($message)
{
    
    if(defined('SUPPORT_TG_ID'))
    {
        
        foreach (explode(',',SUPPORT_TG_ID) as $tgId)
            vkApi_messagesSend($tgId, $message);
        
    }
    else
    {
        bot_debugger('Поддержка для этого бота не задана!!!

Поступила команда отправить сообщение в поддержку:
'.$message);
    }
    
}

?>
