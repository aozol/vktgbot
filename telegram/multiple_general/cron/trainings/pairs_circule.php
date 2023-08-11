<?php

require_once(dirname(__FILE__).'/../../adm/functions.php');


$page_title='Сообщения о парах';

require_once(dirname(__FILE__).'/../../adm/template/top.php');

echo 'cron "Pairs circule"<br>';

if (!isset($mlistId)) exit;
if (strtotime($dateFinish)<time()) exit;
if (strtotime($dateStart)>time()) exit;

$weekN=floor((time()-strtotime($dateStart))/(3600*24*7));

echo 'Week: '.$weekN.'<br>';


$date=date('Y-m-d H:i:s');

echo 'Date to send: '.$date.'<br>';

   
$sql=mysqli_query($dblink,"SELECT vkId,vkGroupId FROM `".DBP."db` WHERE unsub=0 AND mlistId={$mlistId} ORDER BY vkId"); //WHERE vkId=2204686 GROUP BY vkId

$token_arr=array();

while (list($vkId,$vkGroupId)=mysqli_fetch_array($sql))
{   
   
   $user_info=vkApi_usersGet($vkId);
   if($user_info[0]['username'])
    $users[$vkId]['link']='https://t.me/'.$user_info[0]['username'];
   else
    $users[$vkId]['link']='';
   
   $users[$vkId]['name']=$user_info[0]['first_name']; 
   $users[$vkId]['fullname']=$user_info[0]['first_name'].' '.$user_info[0]['last_name'];
   $users[$vkId]['vkGroupId']=$vkGroupId;
   $usersArray[]=$vkId;
   
   if(!isset($token_arr["{$vkGroupId}"]))
   {
    $sql2=mysqli_query($dblink,"SELECT token FROM `".DBP."vkApi` WHERE vkGroupId={$vkGroupId}");
    list($t)=mysqli_fetch_array($sql2);
    $token_arr[(string) $vkGroupId]=$t;
   }

   
}  

$usersN=sizeof($usersArray);

echo 'usersN: '.$usersN.'<br>';

if($weekN>($usersN-1))
    $weekN=$weekN%($usersN-1);

//print_r($usersArray);    
$usersCircule=$usersArray;
for($i=0;$i<$weekN; $i++)
    $usersCircule=array_merge($usersCircule,$usersArray);

if($usersN%($weekN+1)==0)
    $add=1;
else
    $add=0;


$sortArray=array();
$usedUsers=array();
$k=0;

for($i=0;$i<($weekN+1)*$usersN;)
{
    echo $usersCircule[$i].'<br>';
    
    if(in_array($usersCircule[$i],$usedUsers))
    {
        echo 'next <br>';
        
        $i++;
        $add++;
        $k++;
        
        
    }
    
    echo $usersCircule[$i].'<br>';
    
    $usedUsers[]=$usersCircule[$i];
    $sortArray[$k][]=$usersCircule[$i];
    $i+=$weekN+1;
    
    
}



$from[]='%name%';
$from[]='%name_pre%';
$from[]='%link_pre%';
$from[]='%name_post%';
$from[]='%link_post%';

$debug_message='Pairs circule bot works
';
$adm_message='';

foreach($sortArray as $k=>$sA)
{

        $n=sizeof($sA);
        $to[0]=$users[$sA[0]]['name'];
        $to[1]=$users[$sA[$n-1]]['fullname'];
        $to[2]=$users[$sA[$n-1]]['link'];
        $to[3]=$users[$sA[1]]['fullname'];
        $to[4]=$users[$sA[1]]['link'];
        
        $personalText=str_replace($from,$to,$text);
        
        $l=$k*floor($usersN/($weekN+1));
        
        message_queue($token_arr["{$users[$sA[0]]['vkGroupId']}"],$sA[0],$personalText,$date,'');
        
        $adm_message.="<br><br>Text for user {$sA[0]}:<br>{$personalText}";
        $debug_message.="
        Pairs for user {$sA[0]} sent";

    for($i=1;$i<$n-1;$i++)
    {
        
        $to[0]=$users[$sA[$i]]['name'];
        $to[1]=$users[$sA[$i-1]]['fullname'];
        $to[2]=$users[$sA[$i-1]]['link'];
        $to[3]=$users[$sA[$i+1]]['fullname'];
        $to[4]=$users[$sA[$i+1]]['link'];
        
        $personalText=str_replace($from,$to,$text);
        
        
        message_queue($token_arr["{$users[$sA[$i]]['vkGroupId']}"],$sA[$i],$personalText,$date,'');
        $adm_message.="<br><br>Text for user {$sA[$i]}:<br>{$personalText}";
        $debug_message.="
        Pairs for user {$sA[$i]} sent";
    }

        $to[0]=$users[$sA[$n-1]]['name'];
        $to[1]=$users[$sA[$n-2]]['fullname'];
        $to[2]=$users[$sA[$n-2]]['link'];
        $to[3]=$users[$sA[0]]['fullname'];
        $to[4]=$users[$sA[0]]['link'];
        
        $personalText=str_replace($from,$to,$text);
        
        message_queue($token_arr["{$users[$sA[$i]]['vkGroupId']}"],$sA[$i],$personalText,$date,''); 
        $adm_message.="<br><br>Text for user {$sA[$i]}:<br>{$personalText}";
        $debug_message.="
        Pairs for user {$sA[$i]} sent";
        $token=$token_arr["{$users[$sA[$i]]['vkGroupId']}"];

}

bot_debugger($debug_message);

echo $adm_message;

?>
