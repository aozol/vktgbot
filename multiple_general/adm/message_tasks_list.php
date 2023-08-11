<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');


$nowDate=date('Y-m-d H:i:00');

if(isset($_GET['archive']))
{
    $sql=mysqli_query($dblink,"SELECT taskId,dataJson FROM `".DBP_GENERAL."message_tasks` WHERE DBP='".DBP."' AND isDone=1 ORDER BY dateTime DESC");
    $page_title='Архив отправленных сообщений';
    $more_link='<a href="?f=message_tasks_list">Смотреть запланированные</a>';
}
else
{
    $sql=mysqli_query($dblink,"SELECT taskId,dataJson FROM `".DBP_GENERAL."message_tasks` WHERE DBP='".DBP."' AND isDone=0 ORDER BY dateTime ASC");
    $page_title='Список запланированных сообщений';
    $more_link='<a href="?f=message_tasks_list&archive=1">Смотреть архив (отправленные)</a>';
}


require_once(dirname(__FILE__).'/template/top.php');

echo "<h1>{$page_title}</h1>";

echo '<table border=1>
<tr><th>Дата отправки</th><th>Текст</th><th>Управление</th></tr>';

  
while (list($taskId,$dataJson)=mysqli_fetch_array($sql))
{
        $dataArray=json_decode($dataJson, TRUE);
        
        echo "<tr><td>{$dataArray['dateTime']}</td><td>{$dataArray['text']}</td><td><a href=\"?f=message_to_list&taskId={$taskId}\">Изменить</a><br><br><a href=\"?f=message_to_list&taskId={$taskId}&copy=1\">Копировать</a></td></tr>";
}

echo '</table>'.$more_link;

require_once(dirname(__FILE__).'template/bottom.php');

?>
