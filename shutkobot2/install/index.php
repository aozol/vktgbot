<?php

require_once(dirname(__FILE__).'/../conf.php');
require_once(GENERAL_DIR.'/adm/functions.php');
require_once(dirname(__FILE__).'/../functions.php');

$page_title='Установка';

if(isset($_POST['vkGroupId']))
{
    
    $token=$service_token;
    $gId=get_vkGroup_ids($_POST['vkGroupId'],'string');
    
    setcookie("vkGroupId",$gId,time()-10000);
    setcookie("vkGroupId",$gId,0x6FFFFFFF);
    header('Location: https://oauth.vk.com/authorize?client_id=7040402&display=page&redirect_uri=https://vkbot.aozol.ru/shutkobot2/install/&group_ids='.$gId.'&scope=manage&response_type=code&v=5.80');
    
}

elseif(isset($_GET['code']))
{

    require_once(dirname(__FILE__).'/template/top.php');


    $token_arr=json_decode(file_get_contents('https://oauth.vk.com/access_token?client_id=7040402&redirect_uri=https://vkbot.aozol.ru/shutkobot2/install/&client_secret='.$client_secret.'&code='.$_GET['code']),TRUE);
    
        
    
    $token=$token_arr["access_token_{$_COOKIE['vkGroupId']}"];
    
    //echo $token;
    
    $sql=mysqli_query($dblink,"SELECT confirmToken,secret FROM `".DBP."vkApi` WHERE vkGroupId={$_COOKIE['vkGroupId']}");
    
    if(list($confirmationToken,$secretKey)=mysqli_fetch_array($sql)) // если группа уже добавлена - завершаем настройку
    {
        mysqli_query($dblink,"UPDATE `".DBP."vkApi` SET token='{$token}' WHERE vkGroupId={$_COOKIE['vkGroupId']}");
        
        $sql=mysqli_query($dblink,"SELECT MAX(mlistId)+1 FROM `".DBP."mlists`");
        list($mlistId)=mysqli_fetch_array($sql);
        $params['group_id'] = $_COOKIE['vkGroupId'];
        $result=_vkApi_call('groups.getById', $params);
        
        mysqli_query($dblink,"INSERT INTO `".DBP."mlists` (mlistId,mlistName,isPublic,vkGroupId,isDefault) VALUES ({$mlistId},'Шуткобот {$result[0]['name']}',0,{$_COOKIE['vkGroupId']},1)");
        
        mysqli_query($dblink,"INSERT INTO `".DBP."starts` (mlistId,startText,startDate,isActive) VALUES ({$mlistId},'Это только начало, ','".date("Y-m-d 00:00:00",time()-3600*24*2)."',0)");
        $sql=mysqli_query($dblink,"SELECT LAST_INSERT_ID() AS LID");
        list($startId)=mysqli_fetch_array($sql);
        mysqli_query($dblink,"INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'шутки скоро будут',0)");
        
        mysqli_query($dblink,"INSERT INTO `".DBP."starts` (mlistId,startText,startDate,isActive) VALUES ({$mlistId},'Это тестовая шутка ','".date("Y-m-d 00:00:00",time()-3600*24)."',0)");
        $sql=mysqli_query($dblink,"SELECT LAST_INSERT_ID() AS LID");
        list($startId)=mysqli_fetch_array($sql);
        mysqli_query($dblink,"INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'- за нее можно попробовать проголосовать',0)");
        
        echo "<h1>Подключение Шуткобота к группе, шаг 3</h1><p>Настройка шуткбота почти завершена! Осталось задать несколько параметров:</p>
        
        <br>".'
        <form action="index.php" method="post">
        <p>Администраторы Шуткобота (ссылки на страницы ВК, по одной на строке)<br>
        <strong>Не забудьте добавить себя! ;)</strong>
        <br>
        <textarea name="admin" style="height: 50px; width: 200px;"></textarea></p>
    <p>Команда кнопки выхода из Шуткобота*: <input type="text" name="returnPayload" value="0"><br>
    * Это может понадобиться, если к группе подключены несколько ботов и надо между ними переключаться. Если в группе будет только Шуткобот, оставьте значение 0</p>
    <p>Возрастное ограничение: <select name="age"><option value="1">Только 18+
<option value="0">Без ограничений</p>
    <p><input type="submit" value="Сохранить"></p>
    </form>';
    }
    
    else //первичная настройка группы
    {
        
        $result=_vkApi_call('groups.getCallbackConfirmationCode', array(
        'group_id'    => $_COOKIE['vkGroupId'])
        );
        
        $confirmToken=$result['code'];
        $secret_key=hash_pass($_COOKIE['vkGroupId']);
        
        mysqli_query($dblink,"DELETE FROM `".DBP."vkApi` WHERE vkGroupId={$_COOKIE['vkGroupId']}");
    mysqli_query($dblink,"INSERT INTO `".DBP."vkApi` (vkGroupId,token,confirmToken,secret) VALUES ({$_COOKIE['vkGroupId']},'','{$confirmToken}','{$secret_key}')");
        
        $result=_vkApi_call('groups.addCallbackServer', array(
        'group_id'    => $_COOKIE['vkGroupId'],
        'url'    => 'https://vkbot.aozol.ru/shutkobot2/chat_processor.php',
        'title' => 'Шуткобот',
        'secret_key' => $secret_key)
        );
        
        //print_r($result);
        
        if($result['error_code'])
        {
            mysqli_query($dblink,"DELETE FROM `".DBP."vkApi` WHERE vkGroupId={$_COOKIE['vkGroupId']}");
            
            setcookie("vkGroupId",$gId,time()-10000);
            
            echo '<h1>Ошибка при создании сервера Callback API!</h1><p>Попробуйте <a href="index.php">начать заново</a> или использовать другую группу.</p>';
            
            exit;
        }
        
        $serverId=$result['server_id'];
        
        echo "<h1>Подключение Шуткобота к группе, шаг 2</h1><p>Сервер создан и настроен. Идентификатор добавленного сервера: {$serverId}</p>";
        
        $result=_vkApi_call('groups.setCallbackSettings', array(
        'group_id'    => $_COOKIE['vkGroupId'],
        'server_id'    => $serverId,
        'api_version'    => '5.131',
        'message_new'    => 1)
        );
        
        echo "<p>Токен '{$token}' с правами доступа к управлению сообществом больше не нужен, можете удалить его <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=tokens\" target=\"_vk\">здесь</a><br>
        На следующем этапе понадобятся только права на отправку сообщений, чтобы Шуткобот мог общаться с пользователями.</p>
        
        <br>".'
        <form action="index.php" method="post">
    <input type="hidden" name="getMessages" value="1">
    <p><input type="submit" value="Продолжить"></p>
    </form>';
    }
    
    require_once(dirname(__FILE__).'/template/bottom.php');
}

elseif(isset($_POST['admin']))
{
    require_once(dirname(__FILE__).'/template/top.php');
    
    mysqli_query($dblink,"INSERT INTO `".DBP."shutkobotParams` (vkGroupId,returnPayload,age) VALUES ({$_COOKIE['vkGroupId']},'{$_POST['returnPayload']}',{$_POST['age']})");
    
    mysqli_query($dblink,"DELETE FROM `".DBP."shutkobotAdmin` WHERE vkGroupId={$_COOKIE['vkGroupId']}");
    
    $vkIds=get_vk_ids($_POST['admin']);
    
    foreach ($vkIds as $vkId)
    {
        mysqli_query($dblink,"INSERT INTO `".DBP."shutkobotAdmin` (vkGroupId,vkId) VALUES ({$_COOKIE['vkGroupId']},{$vkId})");
    }
    
    echo "<h1>Подключение Шуткобота к группе - финиш</h1><p>Поздравляем, Шуткобот подключен к <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}\" target=\"_vk\">Вашей группе</a>!</p>
    <p><strong style=\"color: red\">Проверьте <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=messages\" target=\"_vk1\">вот здесь</a>, что включены сообщения и <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=messages&tab=bots\" target=\"_vk2\">вот здесь</a>, что включены возможности ботов</strong>!</p>
    <p>Теперь вы можете перейти в <a href=\"../admin\">панель администратора</a> и настроить его</p>";
    
    require_once(dirname(__FILE__).'/template/bottom.php');
}

elseif(isset($_POST['getMessages']))
{
    
    header('Location: https://oauth.vk.com/authorize?client_id=7040402&display=page&redirect_uri=https://vkbot.aozol.ru/shutkobot2/install/&group_ids='.$_COOKIE['vkGroupId'].'&scope=messages&response_type=code&v=5.80');
    
}

else
{

require_once(dirname(__FILE__).'/template/top.php');
?>
<h1>Подключение Шуткобота к группе, шаг 1</h1>
<p>Чтобы подключить Шуткбота к группе/паблику у вас должны быть права администратора.<br/>
Рекомендуем <a href="https://vk.com/groups_create" target="_vk">создать отдельную группу/паблик</a> для вашего Шуткобота!</p>
<p>Подключение происходит в несколько этапов.<br/>
<strong>Право на управление сообществом необходимо для первичной настройки</strong> Callback API. Бот <strong>не хранит</strong> и не использует этот ключ после настройки, вы можете его удалить сразу после следующего шага.</p>
<form action="" method="post">
<p>Ссылка на группу, к которой будем подключать Шуткобота: <input type="text" name="vkGroupId" value=""></p>
<p><input type="submit" value="Подключить"></p>
</form><br><br>

<?php
require_once(dirname(__FILE__).'/template/bottom.php');

}



  ?>
