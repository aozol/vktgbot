<?php

require_once(dirname(__FILE__).'/../conf.php');
require_once(GENERAL_DIR.'/adm/functions.php');
require_once(dirname(__FILE__).'/../functions.php');

if(isset($bot_title))
    $page_title="Управление администраторами | {$bot_title}";
else
    $page_title="Управление администраторами | Бот для vk.com";
    
require_once(dirname(__FILE__).'/template/top.php');

if(in_array($_COOKIE['vkId'],explode(',',ADM_VK_ID)))
    $sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkApi` WHERE vkGroupId={$_GET['vkGroupId']}");
else
    $sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']} AND vkGroupId={$_GET['vkGroupId']}");



if(!mysqli_num_rows($sql))
{
    echo '<p><strong>У Вас нет прав на управление указанной группой</strong></p>';
    require_once('template/bottom.php');
    exit;
}

$token=$service_token;
$params['group_id'] = $_GET['vkGroupId'];
$result=_vkApi_call('groups.getById', $params);
    
echo '<h1>Управление администраторами группы <a href="https://vk.com/club'.$_GET['vkGroupId'].'" target="_vk">'.$result[0]['name'].'</a></h1>';

if(isset($_POST['newAdmin']))
{
    
    foreach ($_POST['action'] as $vkId=>$action)
        if($action=='del')
            mysqli_query($dblink,"DELETE FROM `".DBP."vkAdmin` WHERE vkId={$vkId} AND vkGroupId={$_GET['vkGroupId']}");
    
    if ($_POST['newAdmin'])
    {
        $vkIds=get_vk_ids($_POST['newAdmin']);
        foreach ($vkIds as $vkId)
            mysqli_query($dblink,"INSERT INTO `".DBP."vkAdmin` (vkGroupId,vkId) VALUES ({$_GET['vkGroupId']},{$vkId})");
    
    }
    
    echo '<p><strong style="color:red">Изменения внесены</strong></p>';
    
}

if(isset($_GET['vkGroupId']))
{
    $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."vkAdmin` WHERE vkGroupId={$_GET['vkGroupId']}");
    
    
    $action_list='<option value="nothing">Не менять
    <option value="del">Удалить';
    
    
    echo '
    <form action="" method="post">
    <div class="col w7"><div class="content">
<div class="box header">
<div class="head"><div></div></div>

<div class="desc">
    <table><tr><th>Текущий админ</th><th>Действие</th></tr>';
    
    while (list($vkId)=mysqli_fetch_array($sql))
    {   
        $user_info=vkApi_usersGet($vkId);
        echo '<tr><td><a href="https://vk.com/id'.$vkId.'" target="_vk">'.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].'</a></td><td><select name="action['.$vkId.']">'.$action_list.'</select></td></tr>';
    }
    
    echo '</table></div>
<div class="bottom"><div></div></div>
</div> 
</div></div>

    <div class="col w3"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Новые админы (по одному на строке)</h2>
<div class="desc">
<textarea name="newAdmin" style="width: 200px; height: 100px">'.$text.'</textarea>
</div>
<div class="bottom"><div></div></div>
</div> 
</div></div>
<div class="col w10"><div class="content">
<input type="submit" value="Сохранить"/>
</div></div>
    ';

}



else
{

    echo 'error';

}

require_once(dirname(__FILE__).'/template/bottom.php');

?>
