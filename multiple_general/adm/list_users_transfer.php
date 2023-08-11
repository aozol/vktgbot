<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Перенос из одного списка подписчиков в другой';

require_once(dirname(__FILE__).'/template/top.php');



if (!isset($_GET['mlistId'])) exit;
else
   $mlistId=$_GET['mlistId'];

   
$sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
while (list($mlId,$name)=mysqli_fetch_array($sql))
   $mlistName[$mlId]=$name;
   

$mlistsList='';

$sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");

   
while (list($mlId,$mlName)=mysqli_fetch_array($sql))
{
   
   $mlistsList.= "<option value=\"{$mlId}\">{$mlName}";
   
}


if(isset($_POST))
{
   
   $newmlistId=$_POST['newmlistId'];
      
   foreach ($_POST['action'] as $vkId=>$action)
   {
      if($_POST['all_action'])
        $action=$_POST['all_action'];
      
      
      $sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."db` WHERE vkId={$vkId} AND mlistId={$mlistId}");
      list($vkGroupId)=mysqli_fetch_array($sql);
      
      //mysqli_query($dblink,"INSERT INTO `".DBP."db` (mlistId,vkId,unsub,vkGroupId) VALUES ({$newmlistId},{$vkId},0,{$vkGroupId})");
      
      //echo "INSERT INTO `".DBP."db` (mlistId,vkId,unsub,vkGroupId) VALUES ({$newmlistId},{$vkId},0,{$vkGroupId})".'<br>';
      
      
      user_mlist_manage($vkId,$newmlistId,1,$vkGroupId);
      
      switch ($action)
      {
         case 'unsub':
            
            
            mysqli_query($dblink,"UPDATE `".DBP."db` SET unsub=1 WHERE mlistId={$mlistId} AND vkId={$vkId}");
            echo 'vkId '.$vkId.' unsubscribed and moved<br>'."INSERT INTO `".DBP."db` (mlistId,vkId,unsub,vkGroupId) VALUES ({$newmlistId},{$vkId},0,{$vkGroupId})<br>";
         break;
         
         case 'del':
           mysqli_query($dblink,"DELETE FROM `".DBP."db` WHERE mlistId={$mlistId} AND vkId={$vkId}");
           echo 'vkId '.$vkId.' deleted and moved<br>'."INSERT INTO `".DBP."db` (mlistId,vkId,unsub,vkGroupId) VALUES ({$newmlistId},{$vkId},0,{$vkGroupId})<br>";
         break;
         
         case 'leave':
           echo 'vkId '.$vkId.'  moved<br>'."INSERT INTO `".DBP."db` (mlistId,vkId,unsub,vkGroupId) VALUES ({$newmlistId},{$vkId},0,{$vkGroupId})<br>";
         break;
      }
   }
   
   
}
   
$sql=mysqli_query($dblink,"SELECT vkId,vkGroupId FROM `".DBP."db` WHERE unsub=0 AND mlistId={$mlistId}"); //WHERE vkId=2204686 GROUP BY vkId

echo '<p>Список "'.$mlistName[$mlistId].'"</p>
<form action="" method="post">
<p>Общее действие для всех: <select name="all_action">
<option value="0">Нет (индивидуально для каждого)
<option value="del">Перенести с удалением из текущей
<option value="unsub">Перенести с отпиской от текущей
<option value="leave">Перенести и оставить в текущей
</select></p>';


$users_table='<table border=0><tr><th>Подписчик</th><th>Группа</th><th>Действие</th><th>vkId</th></tr>';
$action_list='
<option value="del">Перенести с удалением из текущей
<option value="unsub">Перенести с отпиской от текущей
<option value="leave">Перенести и оставить в текущей
<option value="nothing">Не менять';

while (list($vkId,$vkGroupId)=mysqli_fetch_array($sql))
{   
   $user_info=userInfo($vkId);
   $users_table.='<tr><td><a href="https://vk.com/id'.$vkId.'" target="_vk">'.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].'</a></td>';   
   $users_table.='<td>'.$groupName[$vkGroupId].'</td>';   
   $users_table.='<td><select name="action['.$vkId.']">'.$action_list.'</select></td>';
   $users_table.='<td>'.$vkId.'</td></tr>';
}  


echo $users_table.'</table>';
echo '<p>Добавить подписчиков в спиок: <select name="newmlistId">'.$mlistsList.'</select><br>
<input type="submit" value="Сохранить изменения" /></p>
</form>';


require_once(dirname(__FILE__).'template/bottom.php');

?>
