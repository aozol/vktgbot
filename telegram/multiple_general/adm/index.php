<?php
require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Главная страница настроек';

require_once(dirname(__FILE__).'/template/top.php');

echo '<h1>Используемые группы</h1>';

if ($adm_info['root'])
    $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE vkGroupId!=-1");
else
    $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE vkGroupId!=-1 AND admin={$adm_info['id']}");

echo '<table border=1>';

if ($adm_info['root'])
    echo "<tr><th>ID бота</th><th>Редактирование</th><th>Ответы бота</th></tr>";
else
    echo "<tr><th>ID бота</th><th>Ответы бота</th></tr>";
   
while (list($vkGroupId,$token)=mysqli_fetch_array($sql))
{
    $params['group_id'] = $vkGroupId;
    //$result=_vkApi_call('groups.getById', $params);
    if ($adm_info['root'])
        echo "<tr><td>{$vkGroupId}</td><td><a href=\"?f=groups&gId={$vkGroupId}\">Редактировать информацию группы</a></td><td><a href=\"?f=bot_reply&gId={$vkGroupId}\">Редактировать сообщения бота</a></td></tr>";
    else
        echo "<tr><td>{$result[0]['name']}</td><td><a href=\"?f=bot_reply&gId={$vkGroupId}\">Редактировать сообщения бота</a></td></tr>";
}

echo '</table>';
if ($adm_info['root'])
    echo '
    <p><a href="?f=groups">Добавить новую</a></p>
    <p><a href="?f=bot_reply&gId=-1">Редактировать общие сообщения бота для всех групп</a></p>';

echo '<br><br>';

if ($adm_info['root'])
    $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."db` WHERE unsub=0");
else
    $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."db` WHERE unsub=0 AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}))");
    

list($n)=mysqli_fetch_array($sql_n);

echo '<h1>Списки рассылки</h1>';

echo '<p>Общее количество подписчиков: '.$n.'</p>';

if ($adm_info['root'])
{    
    if (isset($_GET['delList']))
        mysqli_query($dblink,"DELETE FROM `".DBP."mlists` WHERE mlistId={$_GET['delList']}");
    
    $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
}
else
    $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']})");
    
echo '<table border=1>';

if ($adm_info['root'])
    echo "<tr><th>id</th><th>Список</th><th>Кол-во подписчиков</th><th>Действия</th><th>Подписчики</th><th>Подписчики</th></tr>";
else
    echo "<tr><th>id</th><th>Список</th><th>Кол-во подписчиков</th><th>Подписчики</th><th>Подписчики</th></tr>";

if (!isset($mlistAction))
	$mlistAction='';
else
	$mlistAction='<br>'.$mlistAction;
   
while (list($mlistId,$mlistName)=mysqli_fetch_array($sql))
{
    $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."db` WHERE mlistId={$mlistId} AND unsub=0");
    list($n)=mysqli_fetch_array($sql_n);
    if ($adm_info['root'])
    {
        echo "<tr><td>{$mlistId}</td><td>{$mlistName}</td><td>{$n}</td><td><a href=\"?f=mlists&mlistId={$mlistId}\">Настройки списка</a>".str_replace('%mlistId%',$mlistId,$mlistAction)."</td><td><a href=\"?f=list_users&mlistId={$mlistId}\">Список подписчиков</a></td><td><a href=\"?f=list_users_transfer&mlistId={$mlistId}\">Перенос подписчиков</a></td></tr>";
    }
    else
        echo "<tr><td>{$mlistId}</td><td>{$mlistName}</td><td>{$n}</td></td><td><a href=\"?f=list_users&mlistId={$mlistId}\">Список подписчиков</a></td><td><a href=\"?f=list_users_transfer&mlistId={$mlistId}\">Перенос подписчиков</a></td></tr>";
}

echo '</table>';

if ($adm_info['root'])
    echo '<p><a href="?f=mlists">Добавить новый</a></p>';

echo '<br><br>';



// Events

if ($adm_info['root'])
    $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."events_reg_db` WHERE unsub=0");
else
    $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."events_reg_db` WHERE unsub=0 AND vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']})");
    

list($n)=mysqli_fetch_array($sql_n);

echo '<h1>Мероприятия и регистрации</h1>';

echo '<p>Общее количество подписчиков: '.$n.'</p>';

if ($adm_info['root'])
{    
    if (isset($_GET['delEvent']))
        mysqli_query($dblink,"DELETE FROM `".DBP."events` WHERE eventId={$_GET['delEvent']}");
    
    $sql=mysqli_query($dblink,"SELECT eventId,eventName FROM `".DBP."events`");
}
else
    $sql=mysqli_query($dblink,"SELECT eventId,eventName FROM `".DBP."events` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']})");
    
echo '<table border=1>';

if ($adm_info['root'])
    echo "<tr><th>id</th><th>Название мероприятия</th><th>Действия</th><th>Списки регистрации</th><th>Кол-во подписчиков</th><th>Действия</th></tr>";
else
    echo "<tr><th>id</th><th>Название мероприятия</th><th>Действия</th><th>Списки регистрации</th><th>Кол-во подписчиков</th><th>Действия</th></tr>";

   
while (list($eventId,$eventName)=mysqli_fetch_array($sql))
{
    
    
    $sqlRoles=mysqli_query($dblink,"SELECT roleId,roleName FROM `".DBP."events_roles` WHERE eventId={$eventId}");
    
    
    $nRoles=0;
    
    $rolesTable='';
    while (list($roleId,$roleName)=mysqli_fetch_array($sqlRoles))
    {
        $sql_n=mysqli_query($dblink,"SELECT count(DISTINCT vkId) FROM `".DBP."events_reg_db` WHERE eventId={$eventId} AND roleId={$roleId} AND unsub=0");
        
        list($n)=mysqli_fetch_array($sql_n);
        
        $nRoles++;
        if(!$rolesTable)
            $rolesTable.="<td>{$roleName}</td><td>{$n}</td><td><a href=\"?f=event_roles_users&eventId={$eventId}&roleId={$roleId}\">Список подписчиков</a><br/><br><a href=\"?f=event_roles_transfer&eventId={$eventId}&roleId={$roleId}\">Перенос подписчиков</a></td></tr>";
        else
            $rolesTable.="<tr><td>{$roleName}</td><td>{$n}</td><td><a href=\"?f=event_roles_users&eventId={$eventId}&roleId={$roleId}\">Список подписчиков</a><br/><br><a href=\"?f=event_roles_transfer&eventId={$eventId}&roleId={$roleId}\">Перенос подписчиков</a></td></tr>";
    }
    
    if(!$nRoles)
    {
        
        echo "<tr><td rowspan=\"{$nRoles}\">{$eventId}</td><td rowspan=\"{$nRoles}\">{$eventName}</td><td rowspan=\"{$nRoles}\"><a href=\"?f=events&eventId={$eventId}\">Изменить мероприятие</a>
        <br><br>
        <a href=\"?f=events&eventId={$eventId}&copy=1\">Копировать</a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
        
    }
    else
    {
        echo "<tr><td rowspan=\"{$nRoles}\">{$eventId}</td><td rowspan=\"{$nRoles}\">{$eventName}</td><td rowspan=\"{$nRoles}\"><a href=\"?f=events&eventId={$eventId}\">Изменить мероприятие</a><br><br>
        <a href=\"?f=events&eventId={$eventId}&copy=1\">Копировать</a></td>{$rolesTable}";
    }
    list($n)=mysqli_fetch_array($sql_n); 
    
    
    
   
}

echo '</table>';

if ($adm_info['root'])
    echo '<p><a href="?f=events">Добавить новое мероприятие</a></p>';

echo '<br><br>';

// Events end




if ($adm_info['root'])
{
    echo '<h1>Промокоды</h1>';

    $sql=mysqli_query($dblink,"SELECT promoCode,finDate,activationsN,activatedN FROM `".DBP."promocodes` ORDER BY finDate DESC");

    echo '<table border=1>';

    echo "<tr><th>Код</th><th>Дата окончания</th><th>Максимально активаций</th><th>Сейчас активаций</th><th>&nbsp;</th></tr>";
    
    while (list($promoCode,$finDate,$activationsN,$activatedN)=mysqli_fetch_array($sql))
    {
    if(!$activationsN)
        $activationsN='безлимитно';
    
    echo "<tr><td>{$promoCode}</td><td>{$finDate}</td><td>{$activationsN}</td><td>{$activatedN}</td><td><a href=\"?f=promocodes&promoCode={$promoCode}&finDate={$finDate}\">Смотреть/редактировать</a></td></tr>";
    }

    echo '</table>
    <p><a href="?f=promocodes">Добавить новый</a></p><br><br>';

    echo '<h1>Шаблоны промо-кодов</h1><p>(для автоматической генерации личных промокодов для пользователей)</p>';

    $sql=mysqli_query($dblink,"SELECT pcTemplateId,pcTemplateName FROM `".DBP."promocodeTemplates`");

    echo '<table border=1>';

    echo "<tr><th>Шаблон</th><th>&nbsp;</th></tr>";
    
    while (list($pcTemplateId,$pcTemplateName)=mysqli_fetch_array($sql))
    {
    echo "<tr><td>{$pcTemplateName}</td><td><a href=\"?f=promocodeTemplates&pcTemplateId={$pcTemplateId}\">Смотреть/редактировать</a></td></tr>";
    }

    echo '</table>
    <p><a href="?f=promocodeTemplates">Добавить новый</a></p><br><br>';
    
    
    echo '<h1>Редиректы</h1>';

    $sql=mysqli_query($dblink,"SELECT redirId,finLink,finDate,activationsN,activatedN FROM `".DBP."redirects` ORDER BY finDate DESC");

    echo '<table border=1>';

    echo "<tr><th>Конечая ссылка</th><th>Ссылка для рассылки</th><th>Дата окончания</th><th>Максимально активаций</th><th>Сейчас активаций</th><th>&nbsp;</th></tr>";
    
    while (list($redirId,$finLink,$finDate,$activationsN,$activatedN)=mysqli_fetch_array($sql))
    {
    if(!$activationsN)
        $activationsN='безлимитно';
    
    echo "<tr><td>{$finLink}</td><td>https://".BOT_HOST."redirect.php?vkId=%vkId%&botId=%vkGroupId%&redirId={$redirId}</td><td>{$finDate}</td><td>{$activationsN}</td><td>{$activatedN}</td><td><a href=\"?f=redirects&redirId={$redirId}\">Смотреть/редактировать</a></td></tr>";
    }

    echo '</table>
    <p><a href="?f=redirects">Добавить новый</a></p><br><br>';

}

require_once(dirname(__FILE__).'/template/bottom.php');

?>
