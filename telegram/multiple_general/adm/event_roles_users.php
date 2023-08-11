<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование списка подписчиков';

require_once(dirname(__FILE__).'/template/top.php');



if (!isset($_GET['eventId'])) exit;
else
{
   $eventId=$_GET['eventId'];
   $roleId=$_GET['roleId'];
}
   
$sql=mysqli_query($dblink,"SELECT roleName FROM `".DBP."events_roles` WHERE eventId={$eventId} AND roleId={$roleId}");
   
list($roleName)=mysqli_fetch_array($sql);

$sql=mysqli_query($dblink,"SELECT eventName FROM `".DBP."events` WHERE eventId={$eventId}");

list($eventName)=mysqli_fetch_array($sql);
   

$groupsList='';

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkApi`");

   
while (list($vkGroupId)=mysqli_fetch_array($sql))
{
   $params['group_id'] = $vkGroupId;
   //$result=_vkApi_call('groups.getById', $params);
   
   $groupsList.= "<option value=\"{$vkGroupId}\">{$vkGroupId}";
   
   $groupName[$vkGroupId]=$vkGroupId;
}


if(isset($_POST['add_vkId']))
{
   
   if($_POST['add_vkId'])
   {
     $vkId_arr=get_vk_ids($_POST['add_vkId']);
     
     foreach ($vkId_arr as $vkId)
     {
        if(user_eventreg_manage($vkId,$eventId,$roleId,1,$_POST['vkGroupId'])) //vkId,eventId,roleId,action,groupuser_mlist_manage($vkId,$mlistId,1,))
            echo 'New vkId added: '.$vkId.'<br>';
     }
     
     
     
   }
   
   foreach ($_POST['action'] as $id_str=>$action)
   {
      
      if($_POST['all_action'])
        $action=$_POST['all_action'];
      
      list($vkId,$vkGroupId)=explode(',',$id_str);
      
      switch ($action)
      {
         case 'unsub':
            mysqli_query($dblink,"UPDATE `".DBP."events_reg_db` SET unsub=1 WHERE eventId={$eventId} AND roleId={$roleId} AND vkId={$vkId} AND vkGroupId={$vkGroupId}");
            echo 'vkId '.$vkId.' unsubscribed<br>';
         break;
         
         case 'del':
           mysqli_query($dblink,"DELETE FROM `".DBP."events_reg_db` WHERE eventId={$eventId} AND roleId={$roleId} AND vkId={$vkId} AND vkGroupId={$vkGroupId}");
           echo 'vkId '.$vkId.' deleted<br>';
         break;
      }
   }
   
   
}
   
$sql=mysqli_query($dblink,"SELECT vkId,vkGroupId FROM `".DBP."events_reg_db` WHERE unsub=0 AND eventId={$eventId} AND roleId={$roleId}"); //WHERE vkId=2204686 GROUP BY vkId

echo '<p>Список "'.$roleName.' | '.$eventName.'"</p>
<form action="" method="post">

<p>Общее действие для всех пользвателей в списке: <select name="all_action"><option value="0">Нет (индивидуально для каждого)
<option value="unsub">Отписать
<option value="del">Удалить</select></p>
';



$users_table='<table border=0><tr><th>Подписчик</th><th>Группа</th><th>Действие</th><th>vkId</th><th>&nbsp;</th></tr>';
$action_list='<option value="nothing">Не менять
<option value="unsub">Отписать
<option value="del">Удалить';

while (list($vkId,$vkGroupId)=mysqli_fetch_array($sql))
{   
   $user_info=vkApi_usersGet($vkId);
   $users_table.='<tr><td><a href="https://t.me/'.$user_info[0]['username'].'" target="_vk">'.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].'</a></td>';   
   $users_table.='<td>'.$groupName[$vkGroupId].'</td>';   
   $users_table.='<td><select name="action['.$vkId.','.$vkGroupId.']">'.$action_list.'</select></td>';
   $users_table.='<td>'.$vkId.'</td><td><a href="?f=dialog&vkId='.$vkId.'&botId='.$vkGroupId.'">Написать от бота</a></td></tr>';
}  


echo $users_table.'</table>';
echo '<p>Добавить в список vkId (через запятую) от группы <select name="vkGroupId">'.$groupsList.'</select></p><p><textarea name="add_vkId" style="width: 300px; height: 200px"></textarea><br>
<input type="submit" value="Сохранить изменения" /></p>
</form>';


require_once(dirname(__FILE__).'/template/bottom.php');

?>
