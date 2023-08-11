<?php
require_once('../conf.php');

require_once('login.php');
   
$page_title='Добивки - Шуткобот v2';
   
require_once('template/top.php');

$startId=$_GET['startId'];

$sql=mysqli_query($dblink,"SELECT GROUP_CONCAT(vkGroupId) FROM `".DBP."shutkobotAdmin` WHERE vkId={$_COOKIE['vkId']}");
list($vkGroupIds)=mysqli_fetch_array($sql);

$sql=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId IN ({$vkGroupIds}) AND mlistId IN (SELECT mlistId FROM `".DBP."starts` WHERE startId={$startId})");

if(!mysqli_num_rows($sql))
{
    echo '<p><strong>У Вас нет прав на управление Шуткоботом указанной группы</strong></p>';
    require_once('template/bottom.php');
    exit;
}


if (isset ($_POST['finishText']))  //Обработчик сохранения формы
{
   
   foreach ($_POST['finishText'] as $finishId => $finishText)
   {
        if($finishText)
            mysqli_query($dblink,"UPDATE `".DBP."finishes` SET finishText='{$finishText}' WHERE finishId={$finishId}");
        else
            mysqli_query($dblink,"DELETE FROM `".DBP."finishes` WHERE finishId={$finishId}");
   }
      
   
   echo '<p>Изменения внесены</p>';
   
}



$sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");

list($startText)=mysqli_fetch_array($sql);

echo "<h1>Редактирование добивок к зачину \"{$startText}\"</h1>
<p>Чтобы удалить из базы некорректную добивку, сотрите её текст полностью. Также сохранятся все внесённые изменения.</p>";

$sql=mysqli_query($dblink,"SELECT finishId,finishText,vkId FROM `".DBP."finishes` WHERE startId={$startId} ORDER BY avgVote DESC, medVote DESC");

echo '<form action="" method="post">
<table style ="border: 1px solod;" >';

while (list($finishId,$finishText,$vkId)=mysqli_fetch_array($sql))
{
   $user_info=vkApi_usersGet($vkId);
   
   list($avg,$med)=get_avg_med($finishId);
   
   mysqli_query($dblink,"UPDATE `".DBP."finishes` SET avgVote={$avg}, medVote={$med} WHERE finishId={$finishId}");
   
   $sql_v=mysqli_query($dblink,"SELECT count(vote) FROM `".DBP."votes` WHERE finishId={$finishId}");
   
   list($n_votes)=mysqli_fetch_array($sql_v);
   
   echo "<tr><td><textarea name=\"finishText[{$finishId}]\" style=\"height: 50px; width: 200px;\">{$finishText}</textarea> автор: <a href=\"https://vk.com/id{$vkId}\" target=\"_vk\">{$user_info[0]['first_name']} {$user_info[0]['last_name']}</a>. Среднее: {$avg}; Медиана: {$med}; Голосов: {$n_votes}. Оценки: <textarea style=\"width: 40px; height: 60px\">";
   
   $sql_v=mysqli_query($dblink,"SELECT vote FROM `".DBP."votes` WHERE finishId={$finishId}");
   
   while (list($vote)=mysqli_fetch_array($sql_v))
      echo "\n{$vote}";
   
   echo "</textarea></td></tr>";

}


echo '</table><p><input type="submit" value="Сохранить" /></p></form>';

require_once(GENERAL_DIR.'adm/template/bottom.php');
?>
