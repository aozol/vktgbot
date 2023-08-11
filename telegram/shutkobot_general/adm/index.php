<?php
require_once('../conf.php');



if (isset ($_GET['f']))
   require_once($_GET['f'].'.php');
 
else
{
    require_once('functions.php');
    require_once('login.php');
  

    $page_title='Шуткобот v2 - главная страница администратора';    
   
    require_once(dirname(__FILE__).'/template/top.php');
    

    echo '<h1>Используемые группы</h1>';


    if ($adm_info['root'])
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi`");
    else
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}");

    echo '<table border=1>';

    if ($adm_info['root'])
        echo "<tr><th>Группа</th><th>Редактирование</th><th>Удаление</th></tr>";
    else
        echo "<tr><th>Группа</th></tr>";
   
    while (list($vkGroupId,$token)=mysqli_fetch_array($sql))
    {
        $params['group_id'] = $vkGroupId;
        $result=_vkApi_call('groups.getById', $params);
        if ($adm_info['root'])
            echo "<tr><td><a href=\"https://vk.com/club{$vkGroupId}\" target=\"_vk\">{$result[0]['name']}</a></td><td><a href=\"?f=groups&gId={$vkGroupId}\">Редактировать информацию группы</a></td><td><a href=\"../install/uninstall.php?vkGroupId={$vkGroupId}\" target=\"_uninstall\">Удалить шуткобота группы</a></td></tr>";
        else
            echo "<tr><td><a href=\"https://vk.com/club{$vkGroupId}\" target=\"_vk\">{$result[0]['name']}</a></td></tr>";
    }

    echo '</table>';
    if ($adm_info['root'])
        echo '
        <p><a href="?f=groups">Добавить новую</a></p>';

    echo '<br><br>';

    if ($adm_info['root'])
        $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."db` WHERE unsub=0");
    else
        $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."db` WHERE unsub=0 AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}))");
        

    list($n)=mysqli_fetch_array($sql_n);

    echo '<h1>Списки рассылки</h1>';

    echo '<p>Общее количество подписчиков: '.$n.'</p>';

    if ($adm_info['root'])
        $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
    else
        $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']})");
        
    echo '<table border=1>';

    if ($adm_info['root'])
        echo "<tr><th>id</th><th>Список</th><th>Кол-во подписчиков</th><th>Действия</th><th>Подписчики</th><th>Подписчики</th></tr>";
    else
        echo "<tr><th>id</th><th>Список</th><th>Кол-во подписчиков</th><th>Действия</th><th>Подписчики</th><th>Подписчики</th></tr>";

    if (!isset($mlistAction))
        $mlistAction='';
    else
        $mlistAction='<br>'.$mlistAction;
    
    while (list($mlistId,$mlistName)=mysqli_fetch_array($sql))
    {
        $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0");
        list($n)=mysqli_fetch_array($sql_n);
        if ($adm_info['root'])
            echo "<tr><td>{$mlistId}</td><td>{$mlistName}</td><td>{$n}</td><td><a href=\"?f=mlists&mlistId={$mlistId}\">Настройки списка</a><br><a href=\"start.php?mlistId={$mlistId}\">Редактировать зачины для списка</a></td><td><a href=\"?f=list_users&mlistId={$mlistId}\">Список подписчиков</a></td><td><a href=\"?f=list_users_transfer&mlistId={$mlistId}\">Перенос подписчиков</a></td></tr>";
        else
            echo "<tr><td>{$mlistId}</td><td>{$mlistName}</td><td>{$n}</td><td><a href=\"start.php?mlistId={$mlistId}\">Редактировать зачины для списка</a></td><td><a href=\"?f=list_users&mlistId={$mlistId}\">Список подписчиков</a></td><td><a href=\"?f=list_users_transfer&mlistId={$mlistId}\">Перенос подписчиков</a></td></tr>";
    }

    echo '</table>';

    if ($adm_info['root'])
        echo '<p><a href="?f=mlists">Добавить новый</a></p>';

    echo '<br><br>';

    require_once(dirname(__FILE__).'template/bottom.php');
    
}
//*/
?>
