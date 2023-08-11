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
    echo "<tr><th>Группа</th><th>Редактирование</th><th>Ответы бота</th></tr>";
else
    echo "<tr><th>Группа</th><th>Ответы бота</th></tr>";
   
while (list($vkGroupId,$token)=mysqli_fetch_array($sql))
{
    $params['group_id'] = $vkGroupId;
    $result=_vkApi_call('groups.getById', $params);
    if ($adm_info['root'])
        echo "<tr><td><a href=\"https://vk.com/club{$vkGroupId}\" target=\"_vk\">{$result[0]['name']}</a></td><td><a href=\"?f=groups&gId={$vkGroupId}\">Редактировать информацию группы</a></td><td><a href=\"?f=bot_reply&gId={$vkGroupId}\">Редактировать сообщения бота</a></td></tr>";
    else
        echo "<tr><td><a href=\"https://vk.com/club{$vkGroupId}\" target=\"_vk\">{$result[0]['name']}</a></td><td><a href=\"?f=bot_reply&gId={$vkGroupId}\">Редактировать сообщения бота</a></td></tr>";
}

echo '</table>';
if ($adm_info['root'])
    echo '
    <p><a href="?f=install_group">Добавить новую</a></p>
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
    if (isset($_GET['del']))
        mysqli_query($dblink,"DELETE FROM `".DBP."mlists` WHERE mlistId={$_GET['del']}");
    
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

}

require_once(dirname(__FILE__).'template/bottom.php');

?>
