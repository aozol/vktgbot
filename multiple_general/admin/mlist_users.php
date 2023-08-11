<?php

require_once(dirname(__FILE__).'/../functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование списка подписчиков';

require_once(dirname(__FILE__).'/template/top.php');



if (!isset($_GET['mlistId'])) exit;
else
   $mlistId=$_GET['mlistId'];

   
$sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
while (list($mlId,$name)=mysqli_fetch_array($sql))
   $mlistName[$mlId]=$name;
   

$groupsList='';

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']}");

   
while (list($vkGroupId)=mysqli_fetch_array($sql))
{
   $params['group_id'] = $vkGroupId;
   $result=_vkApi_call('groups.getById', $params);
   
   $groupsList.= "<option value=\"{$vkGroupId}\">{$result[0]['name']}";
   
   $groupName[$vkGroupId]=$result[0]['name'];
}


if(isset($_POST['add_vkId']))
{
   
   if($_POST['add_vkId'])
   {
     $vkId_arr=get_vk_ids($_POST['add_vkId']);
     
     foreach ($vkId_arr as $vkId)
     {
        $sql_str="INSERT INTO `".DBP."db` (mlistId,vkId,unsub,vkGroupId) VALUES ({$mlistId},{$vkId},0,{$_POST['vkGroupId']})";
        
        $sql=mysqli_query($dblink,$sql_str);
        
        if(mysqli_affected_rows ($sql))
            echo 'New vkId added: '.$vkId.'<br>';
     }
     
     
     
   }
   
   foreach ($_POST['action'] as $id_str=>$action)
   {
      
      list($vkId,$vkGroupId)=explode(',',$id_str);
      
      switch ($action)
      {
         case 'unsub':
            mysqli_query($dblink,"UPDATE `".DBP."db` SET unsub=1 WHERE mlistId={$mlistId} AND vkId={$vkId} AND vkGroupId={$vkGroupId}");
            echo 'vkId '.$vkId.' unsubscribed<br>';
         break;
         
         case 'del':
           mysqli_query($dblink,"DELETE FROM `".DBP."db` WHERE mlistId={$mlistId} AND vkId={$vkId} AND vkGroupId={$vkGroupId}");
           echo 'vkId '.$vkId.' deleted<br>';
         break;
      }
   }
   
   
}
   
$sql=mysqli_query($dblink,"SELECT vkId,vkGroupId FROM `".DBP."db` WHERE unsub=0 AND mlistId={$mlistId}"); //WHERE vkId=2204686 GROUP BY vkId

echo '<h1>Подписчики - Список "'.$mlistName[$mlistId].'"</h1>
<form action="" method="post">';


$users_table='<table border=0><tr><th>Подписчик</th><th>Группа</th><th>Действие</th><th>vkId</th></tr>';
$action_list='<option value="nothing">Не менять
<option value="unsub">Отписать
<option value="del">Удалить';

while (list($vkId,$vkGroupId)=mysqli_fetch_array($sql))
{   
   $user_info=vkApi_usersGet($vkId);
   $users_table.='<tr><td><a href="https://vk.com/id'.$vkId.'" target="_vk">'.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].'</a></td>';   
   $users_table.='<td>'.$groupName[$vkGroupId].'</td>';   
   $users_table.='<td><select name="action['.$vkId.','.$vkGroupId.']">'.$action_list.'</select></td>';
   $users_table.='<td>'.$vkId.'</td></tr>';
}  


echo $users_table.'</table>';
echo '<p><br>&nbsp;<br><br>Добавить пользователей в список (vkId или ссылки через запятую или по одному на строке):</p>
<p><textarea name="add_vkId" style="width: 300px; height: 200px"></textarea></p><p>Добавить от группы <select name="vkGroupId">'.$groupsList.'</select></p>
<input type="submit" value="Сохранить изменения" /></p>
</form>';


require_once(dirname(__FILE__).'template/bottom.php');

?>
