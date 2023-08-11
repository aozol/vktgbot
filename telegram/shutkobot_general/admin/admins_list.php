<?php

require_once(dirname(__FILE__).'/../conf.php');
require_once(GENERAL_DIR.'/adm/functions.php');
require_once(dirname(__FILE__).'/../functions.php');

$page_title='Управление администраторами Шуткобота';
require_once(dirname(__FILE__).'/template/top.php');

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."shutkobotAdmin` WHERE vkId={$_COOKIE['vkId']} AND vkGroupId={$_GET['vkGroupId']}");



if(!mysqli_num_rows($sql))
{
    echo '<p><strong>У Вас нет прав на управление Шуткоботом указанной группы</strong></p>';
    require_once('template/bottom.php');
    exit;
}

$token=$service_token;
$params['group_id'] = $_GET['vkGroupId'];
$result=_vkApi_call('groups.getById', $params);
    
echo '<h1>Управление администраторами Шуткобота группы <a href="https://vk.com/club'.$_GET['vkGroupId'].'" target="_vk">'.$result[0]['name'].'</a></h1>';

if(isset($_POST['action']))
{
    
    foreach ($_POST['action'] as $vkId=>$action)
        if($action=='del')
            mysqli_query($dblink,"DELETE FROM `".DBP."shutkobotAdmin` WHERE vkId={$vkId} AND vkGroupId={$_GET['vkGroupId']}");
    
    if ($_POST['newAdmin'])
    {
        $vkIds=get_vk_ids($_POST['newAdmin']);
        foreach ($vkIds as $vkId)
            mysqli_query($dblink,"INSERT INTO `".DBP."shutkobotAdmin` (vkGroupId,vkId) VALUES ({$_GET['vkGroupId']},{$vkId})");
    
    }
    
    echo '<p><strong style="color:red">Изменения внесены</strong></p>';
    
}

if(isset($_GET['vkGroupId']))
{
    $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."shutkobotAdmin` WHERE vkGroupId={$_GET['vkGroupId']}");
    
    
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
