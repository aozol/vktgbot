<?php
require_once(dirname(__FILE__).'/login.php');

$title='Управление Шуткоботом v2';
require_once(dirname(__FILE__).'/template/top.php');

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."shutkobotAdmin` WHERE vkId={$_COOKIE['vkId']}");

echo '<h1>Ваши Шуткоботы</h1>
<table border=1>
<tr><th>id бота</th><th>Подписчиков</th><th>Настройки</th><th>Зачины</th></tr>';

while (list($vkGroupId)=mysqli_fetch_array($sql))
{
    $params['group_id'] = $vkGroupId;
    //$result=_vkApi_call('groups.getById', $params);
    
    $sql2=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$vkGroupId}");

    list($mlistId)=mysqli_fetch_array($sql2);
    
    $sql2=mysqli_query($dblink,"SELECT COUNT(vkId) as nVkId FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0");
    //echo "SELECT COUNT (vkId) as nVkId FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0";
    list($n_users)=mysqli_fetch_array($sql2);
    
    echo "<tr><td>{$vkGroupId}</td><td>{$n_users}</td><td><a href=\"admins_list.php?vkGroupId={$vkGroupId}\">Список администраторов</a><br><br><a href=\"uninstall.php?vkGroupId={$vkGroupId}\" target=\"_uninstall\">Удалить шуткобота группы</a></td><td><a href=\"starts.php?mlistId={$mlistId}\">Редактировать зачины и добивки</a><br><a href=\"top.php?mlistId={$mlistId}\">Смотреть топ шуток</a></td></tr>";
}

echo '</table>';
echo "<p><a href=\"../install/\" target=\"_install\">Подключить новую группу</a></p>";
?>



<?
require_once(dirname(__FILE__).'/template/bottom.php');
?>
