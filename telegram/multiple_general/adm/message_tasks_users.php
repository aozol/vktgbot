<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Список получателей рассылки';

require_once(dirname(__FILE__).'/template/top.php');



if (!isset($_GET['taskId'])) 
{
    echo 'No taskId given';
    exit;
}
else
   $taskId=$_GET['taskId'];

   
if ($adm_info['root'])
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi`");
    else
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}");

$vkGroupIdArr=array();
while (list($vkGroupId,$token)=mysqli_fetch_array($sql))
{
    $vkGroupIdArr[$token]=$vkGroupId;
}
   
$sql=mysqli_query($dblink,"SELECT dateTime,dataJson FROM `".DBP_GENERAL."message_tasks` WHERE taskId={$taskId} AND DBP='".DBP."'");
if (list($dateTime,$dataJson)=mysqli_fetch_array($sql))
    $dataArray=json_decode($dataJson, TRUE);
else
    $dataArray=array();


echo '<p>Дата отправки: '.$dataArray['dateTime'].'</p>
<p>Сообщение:</p>

<p><textarea name="text" style="width: 300px; height: 200px">'.$dataArray['text'].'</textarea>';
   
$sql=mysqli_query($dblink,"SELECT vkId,token FROM `".DBP_GENERAL."message_queue` WHERE taskId={$taskId}");

echo '<p>Список получателей рассылки</p>
<form action="" method="post">
';



$users_table='<table border=0><tr><th>Подписчик</th><th>Группа</th><th>vkId</th><th>&nbsp;</th></tr>';

$vkGroupArr=array();
while (list($vkId,$token)=mysqli_fetch_array($sql))
{   
   
   
   $user_info=vkApi_usersGet($vkId);
   $users_table.='<tr><td><a href="https://t.me/'.$user_info[0]['username'].'" target="_vk">'.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].'</a></td>';   
   $users_table.='<td>'.$vkGroupIdArr[$token].'</td>';   
   $users_table.='<td>'.$vkId.'</td><td><a href="?f=dialog&vkId='.$vkId.'&botId='.$vkGroupIdArr[$token].'">Написать от бота</a></td></tr>';
}  


echo $users_table.'</table>';

require_once(dirname(__FILE__).'/template/bottom.php');

?>
