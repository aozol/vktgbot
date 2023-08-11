<?php

require_once('../functions.php');

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование профилей клуба настолок';

require_once(dirname(__FILE__).'/template/top.php');



if(isset($_POST['vkId']))
{
   
   foreach ($_POST['vkId'] as $i=>$vkId)
   {
      
      if(!$_POST['dateFinish'][$i])
         $dateFinish='NULL';
      else
         $dateFinish="'{$_POST['dateFinish'][$i]}'";
      
      mysqli_query($dblink,"UPDATE `".DBP."games_profile` SET nPayed={$_POST['nPayed'][$i]},nVisited={$_POST['nVisited'][$i]},dateFinish={$dateFinish} WHERE vkId={$vkId}");
      
      
   }
   
   
}
   
$sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."db` WHERE unsub=0 AND mlistId=301");

echo '<p>Профили пользователей</p>
<form action="" method="post">

';



$users_table='<table border=0><tr><th>Подписчик</th><th>Оплачено посещений</th><th>Использовано посещений</th><th>Дата окончания абонемента</th><th>Написать от бота</th></tr>';

$i=0;

$vkGroupId=6233790592;

while (list($vkId)=mysqli_fetch_array($sql))
{   
   $user_info=vkApi_usersGet($vkId);
   list($nPayed,$dateFinish,$nVisited)=get_games_profile($vkId);
   
   $users_table.='<tr><td><a href="https://t.me/'.$user_info[0]['username'].'" target="_vk">'.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].'</a><input type="hidden" name="vkId['.$i.']" value="'.$vkId.'" /></td>';   
   $users_table.='<td><input type="text" name="nPayed['.$i.']" value="'.$nPayed.'" /></td>';   
   $users_table.='<td><input type="text" name="nVisited['.$i.']" value="'.$nVisited.'" /></td>';
   $users_table.='<td><input type="text" name="dateFinish['.$i.']" value="'.$dateFinish.'" /></td><td><a href="?f=dialog&vkId='.$vkId.'&botId='.$vkGroupId.'">Написать от бота</a></td></tr>';
   
   $i++;
}  


echo $users_table.'</table>';
echo '
<input type="submit" value="Сохранить изменения" /></p>
</form>';


require_once(dirname(__FILE__).'/template/bottom.php');

?>
