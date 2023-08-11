<?php

require_once(dirname(__FILE__).'/../conf.php');
require_once(GENERAL_DIR.'/adm/functions.php');
require_once(dirname(__FILE__).'/../functions.php');

$page_title='Удаление функционала шуткобота';
require_once(dirname(__FILE__).'/template/top.php');

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."shutkobotAdmin` WHERE vkId={$_COOKIE['vkId']} AND vkGroupId={$_GET['vkGroupId']}");

if(!mysqli_num_rows($sql))
{
    echo '<p><strong>У Вас нет прав на управление Шуткоботом указанной группы</strong></p>';
    require_once('template/bottom.php');
    exit;
}

if(isset($_POST['vkGroupId']))
{
    
    $sql=mysqli_query($dblink,"SELECT GROUP_CONCAT(mlistId) FROM `".DBP."mlists` WHERE vkGroupId={$_POST['vkGroupId']}");
    
    list($mlists)=mysqli_fetch_array($sql);
    
    echo "DELETE FROM `".DBP."vkApi` WHERE vkGroupId={$_POST['vkGroupId']}".'<br>';
    mysqli_query($dblink,"DELETE FROM `".DBP."vkApi` WHERE vkGroupId={$_POST['vkGroupId']}");
    
    echo "DELETE FROM `".DBP."shutkobotAdmin` WHERE vkGroupId={$_POST['vkGroupId']}".'<br>';
    mysqli_query($dblink,"DELETE FROM `".DBP."shutkobotAdmin` WHERE vkGroupId={$_POST['vkGroupId']}");
    
    echo "DELETE FROM `".DBP."shutkobotParams` WHERE vkGroupId={$_POST['vkGroupId']}".'<br>';
    mysqli_query($dblink,"DELETE FROM `".DBP."shutkobotParams` WHERE vkGroupId={$_POST['vkGroupId']}");
    
    $sql=mysqli_query($dblink,"SELECT GROUP_CONCAT(startId) FROM `".DBP."starts` WHERE mlistId IN ($mlists)");
    
    list($startids)=mysqli_fetch_array($sql);
    
    echo "DELETE FROM `".DBP."db` WHERE mlistId IN ($mlists)".'<br>';
    mysqli_query($dblink,"DELETE FROM `".DBP."db` WHERE mlistId IN ($mlists)");
    
    echo "DELETE FROM `".DBP."mlists` WHERE mlistId IN ($mlists)".'<br>';
    mysqli_query($dblink,"DELETE FROM `".DBP."mlists` WHERE mlistId IN ($mlists)");
    
    echo "DELETE FROM `".DBP."starts` WHERE startId IN ($startids)".'<br>';
    mysqli_query($dblink,"DELETE FROM `".DBP."starts` WHERE startId IN ($startids)");
    
    echo "DELETE FROM `".DBP."finishes` WHERE startId IN ($startids)".'<br>';
    mysqli_query($dblink,"DELETE FROM `".DBP."finishes` WHERE startId IN ($startids)");
    
    echo 'Функционал шуткобота в группе удален!';
    
}

elseif(isset($_GET['vkGroupId']))
{
 
    $params['group_id'] = $_GET['vkGroupId'];
    $result=_vkApi_call('groups.getById', $params);
    
    echo '<h1>Удаление из системы группы <a href="https://vk.com/club'.$_GET['vkGroupId'].'" target="_vk">'.$result[0]['name'].'</a></h1>
    <form action="" method="post">
    <p>После подтверждения будут <strong>удалены все данные</strong> для этой группы, включая <strong>список подписчиков, зачины, добивки и их рейтинг</strong>. Вы точно уверены, что хотите удалить группу?</p>
<p><input type="hidden" name="vkGroupId" value="'.$_GET['vkGroupId'].'"></p>
<p><input type="submit" value="Да, я хочу ВСЕ удалить"></p>
</form><br><br>';

}



else
{

    echo 'error';

}

require_once(dirname(__FILE__).'/template/bottom.php');

?>
