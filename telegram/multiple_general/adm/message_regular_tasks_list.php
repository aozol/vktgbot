<?php

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');


$nowDate=date('Y-m-d H:i:00');
$info_message='';

if(isset($_GET['isDone']))
{
    mysqli_query($dblink,"UPDATE `".DBP_GENERAL."message_regular_tasks` SET isDone={$_GET['isDone']} WHERE DBP='".DBP."' AND taskId={$_GET['taskId']}");

    if($_GET['isDone'])
        $info_message='<p style="color: red;"><strong>Регулярное сообщение отключено</strong></p>';
    else
        $info_message='<p style="color: red;"><strong>Регулярное сообщение включено</strong></p>';
}

if(isset($_GET['archive']))
{
    $sql=mysqli_query($dblink,"SELECT taskId,month,day,weekday,hour,minute,dataJson FROM `".DBP_GENERAL."message_regular_tasks` WHERE DBP='".DBP."' AND isDone=1");
    $page_title='Архив регулярных сообщений';
    $archive_link="&archive=1";
    $more_link='<a href="?f=message_regular_tasks_list">Смотреть активные</a>';
    $switch_link='isDone=0';
    $switch_link_text='Включить сообщение';
}
else
{
    $sql=mysqli_query($dblink,"SELECT taskId,month,day,weekday,hour,minute,dataJson FROM `".DBP_GENERAL."message_regular_tasks` WHERE DBP='".DBP."' AND isDone=0");
    $page_title='Список регулярных сообщений';
    $archive_link="";
    $more_link='<a href="?f=message_regular_tasks_list&archive=1">Смотреть архив (отключенные)</a>';
    $switch_link='isDone=1';
    $switch_link_text='Отключить сообщение';
}


require_once(dirname(__FILE__).'/template/top.php');

echo "<h1>{$page_title}</h1>";

echo $info_message;

echo '<table border=1>
<tr><th>Время отправки</th><th>Текст</th><th>Управление</th></tr>';

  
while (list($taskId,$month,$day,$weekday,$hour,$minute,$dataJson)=mysqli_fetch_array($sql))
{
        $dataArray=json_decode($dataJson, TRUE);
        
        echo "<tr><td>Месяц: {$month}<br>День месяца: {$day}<br>День недели: {$weekday}<br>Время: {$hour}:{$minute}</td><td>{$dataArray['text']}</td><td><a href=\"?f=regular_message&taskId={$taskId}\">Изменить</a><br><br><a href=\"?f=regular_message&taskId={$taskId}&copy=1\">Копировать</a><br><br><a href=\"?f=message_regular_tasks_list&taskId={$taskId}&{$switch_link}{$archive_link}\">{$switch_link_text}</a></td></tr>";
}

echo '</table>'.$more_link.'<br/><a href="?f=regular_message">Добавить новое регулярное сообщение</a>';

require_once(dirname(__FILE__).'template/bottom.php');

?>
