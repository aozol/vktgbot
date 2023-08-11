<?php

/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/

require_once(dirname(__FILE__).'/login.php');

if(isset($bot_title))
    $title="Главная | {$bot_title}";
else
    $title="Главная | Бот для vk.com";

    
require_once(dirname(__FILE__).'/template/top.php');


$vkGroupsArr=array();

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']}");



if(list($vkGroupId)=mysqli_fetch_array($sql))
{

    echo '<h1>Ваши группы</h1>
    <table border=1>
    <tr><th>Группа</th><th>Настройки</th></tr>';
    
    echo "<tr><td>$vkGroupId</td><td><a href=\"?f=admins_list&vkGroupId={$vkGroupId}\">Управление администраторами</a></td></tr>";
    $vkGroupsArr[$vkGroupId]=$vkGroupId;

    while(list($vkGroupId)=mysqli_fetch_array($sql))
    {
        $vkGroupsArr[$vkGroupId]=$vkGroupId;
        
        echo "<tr><td>$vkGroupId</td><td><a href=\"?f=admins_list&vkGroupId={$vkGroupId}\">Управление администраторами</a></td></tr>";
    }

    echo '</table>
    <p>&nbsp;<br></p>';
}

echo '<h1>Ваши листы подписки</h1>
<table border=1>
<tr><th>Группа</th><th>Список</th><th>Подписчиков</th><th>Управление</th></tr>';

foreach($vkGroupsArr as $vkGroupId => $vkGroupName)
{
    
    
    $sql2=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']}) OR  (vkGroupId=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']}) GROUP BY mlistId))");
    
    
    while (list($mlistId,$mlistName)=mysqli_fetch_array($sql2))
    {
    
        $sql3=mysqli_query($dblink,"SELECT COUNT(vkId) as nVkId FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0");
        //echo "SELECT COUNT (vkId) as nVkId FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0";
        list($n_users)=mysqli_fetch_array($sql3);
        
        echo "<tr><td><a href=\"https://vk.com/club{$vkGroupId}\" target=\"_vk\">{$vkGroupName}</a></td><td>{$mlistName}</td><td>{$n_users}</td><td><a href=\"?f=mlist_manage&mlistId={$mlistId}\">Параметры списка</a><br/>
        <a href=\"?f=mlist_users&mlistId={$mlistId}\">Список подписчиков</a><br/>
        <a href=\"?f=mlist_users_transfer&mlistId={$mlistId}\">Перенос/копирование подписчиков</a></td></tr>";
    }
}

echo '</table>
<p><a href="?f=mlist_manage">Добавить новый список</a></p>';


if($_COOKIE['vkId']==ADM_VK_ID)
{

    $sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkApi`");

    echo '<h1>Все листы подписки (администрирование)</h1>
    <table border=1>
    <tr><th>Группа</th><th>Список</th><th>Подписчиков</th><th>Настройки</th></tr>';

    while (list($vkGroupId)=mysqli_fetch_array($sql))
    {
        //$params['group_id'] = $vkGroupId;
        //$result=_vkApi_call('groups.getById', $params);
        
        $sql2=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId={$vkGroupId} OR  (vkGroupId=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkGroupId={$vkGroupId} GROUP BY mlistId))");
        
        while (list($mlistId,$mlistName)=mysqli_fetch_array($sql2))
        {
        
            $sql3=mysqli_query($dblink,"SELECT COUNT(vkId) as nVkId FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0");
            //echo "SELECT COUNT (vkId) as nVkId FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0";
            list($n_users)=mysqli_fetch_array($sql3);
            
            echo "<tr><td><a href=\"https://vk.com/club{$vkGroupId}\" target=\"_vk\">{$vkGroupId}</a></td><td>{$mlistName}</td><td>{$n_users}</td><td><a href=\"?f=admins_list&vkGroupId={$vkGroupId}\">Список администраторов группы</a></td></tr>";
        }
    }

    echo '</table>';
}



require_once(dirname(__FILE__).'/template/bottom.php');
?>
