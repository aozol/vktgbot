<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$service_token=VK_SERVICE_TOKEN;
$client_secret=VK_CLIENT_SECRET;
$app_id=VK_APP_ID;

if(isset($_POST['vkGroupId']))
{
    
    $token=$service_token;
    $gId=get_vkGroup_ids($_POST['vkGroupId'],'string');
    
    setcookie("vkGroupId",$gId,time()-10000);
    setcookie("vkGroupId",$gId,0x6FFFFFFF);
    header('Location: https://oauth.vk.com/authorize?client_id='.$app_id.'&display=page&redirect_uri=https://'.BOT_HOST.'adm/?f=install_group&group_ids='.$gId.'&scope=manage&response_type=code&v=5.80');
    
}

elseif(isset($_GET['code']))
{

    $page_title='Установка новой группы VK';
    
    require_once(dirname(__FILE__).'/template/top.php');


    $token_arr=json_decode(file_get_contents('https://oauth.vk.com/access_token?client_id='.$app_id.'&redirect_uri=https://'.BOT_HOST.'adm/?f=install_group&client_secret='.$client_secret.'&code='.$_GET['code']),TRUE);
    
        
    
    $token=$token_arr["access_token_{$_COOKIE['vkGroupId']}"];
    
    $sql=mysqli_query($dblink,"SELECT confirmToken,secret FROM `".DBP."vkApi` WHERE vkGroupId={$_COOKIE['vkGroupId']}");
    
    if(list($confirmationToken,$secretKey)=mysqli_fetch_array($sql)) // если группа уже добавлена - завершаем настройку
    {
        if($token) mysqli_query($dblink,"UPDATE `".DBP."vkApi` SET token='{$token}' WHERE vkGroupId={$_COOKIE['vkGroupId']}");
        
        
        
            require_once(dirname(__FILE__).'/template/top.php');
    
            echo "<h1>Подключение бота к группе - финиш</h1><p>Поздравляем, бот подключен к <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}\" target=\"_vk\">Вашей группе</a>!</p>
            <p><strong style=\"color: red\">Проверьте <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=messages\" target=\"_vk1\">вот здесь</a>, что включены сообщения и <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=messages&tab=bots\" target=\"_vk2\">вот здесь</a>, что включены возможности ботов</strong>!</p>
            <p>Теперь вы можете перейти в <a href=\"../adm\">панель администратора</a></p>";
    
            require_once(dirname(__FILE__).'/template/bottom.php');
    }
    
    else //первичная настройка группы
    {
        
        $page_title='Установка новой группы VK';
        require_once(dirname(__FILE__).'/template/top.php');

        
        $result=_vkApi_call('groups.getCallbackConfirmationCode', array(
        'group_id'    => $_COOKIE['vkGroupId'])
        );
        
        $confirmToken=$result['code'];
        $secret_key=hash_pass($_COOKIE['vkGroupId']);
        
        mysqli_query($dblink,"DELETE FROM `".DBP."vkApi` WHERE vkGroupId={$_COOKIE['vkGroupId']}");
    mysqli_query($dblink,"INSERT INTO `".DBP."vkApi` (vkGroupId,token,confirmToken,secret) VALUES ({$_COOKIE['vkGroupId']},'','{$confirmToken}','{$secret_key}')");
        
        $result=_vkApi_call('groups.addCallbackServer', array(
        'group_id'    => $_COOKIE['vkGroupId'],
        'url'    => 'https://'.BOT_HOST.'chat_processor.php',
        'title' => 'aozol_vkbot',
        'secret_key' => $secret_key)
        );
        
        //print_r($result);
        
        if($result['error_code'])
        {
            mysqli_query($dblink,"DELETE FROM `".DBP."vkApi` WHERE vkGroupId={$_COOKIE['vkGroupId']}");
            
            setcookie("vkGroupId",$gId,time()-10000);
            
            echo '<h1>Ошибка при создании сервера Callback API!</h1><p>Попробуйте <a href="?f=install_group">начать заново</a> или использовать другую группу.</p>';
            
            exit;
        }
        
        $serverId=$result['server_id'];
        
        echo "<h1>Подключение новой группы, шаг 2</h1><p>Сервер создан и настроен. Идентификатор добавленного сервера: {$serverId}</p>";
        
        $result=_vkApi_call('groups.setCallbackSettings', array(
        'group_id'    => $_COOKIE['vkGroupId'],
        'server_id'    => $serverId,
        'api_version'    => '5.131',
        'message_new'    => 1)
        );
        
        echo "<p>Токен '{$token}' с правами доступа к управлению сообществом больше не нужен, можете удалить его <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=tokens\" target=\"_vk\">здесь</a><br>
        На следующем этапе понадобятся только права на отправку сообщений, чтобы бот мог общаться с пользователями.</p>
        
        <br>".'
        <form action="?f=install_group" method="post">
    <input type="hidden" name="getMessages" value="1">
    <p><input type="submit" value="Продолжить"></p>
    </form>';
    }
    
    require_once(dirname(__FILE__).'/template/bottom.php');
}

elseif(isset($_POST['admin']))
{
    require_once(dirname(__FILE__).'/template/top.php');
    
    echo "<h1>Подключение бота к группе - финиш</h1><p>Поздравляем, бот подключен к <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}\" target=\"_vk\">Вашей группе</a>!</p>
    <p><strong style=\"color: red\">Проверьте <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=messages\" target=\"_vk1\">вот здесь</a>, что включены сообщения и <a href=\"https://vk.com/club{$_COOKIE['vkGroupId']}?act=messages&tab=bots\" target=\"_vk2\">вот здесь</a>, что включены возможности ботов</strong>!</p>
    <p>Теперь вы можете перейти в <a href=\"../adm\">панель администратора</a></p>";
    
    require_once(dirname(__FILE__).'/template/bottom.php');
}

elseif(isset($_POST['getMessages']))
{
    
    header('Location: https://oauth.vk.com/authorize?client_id='.$app_id.'&display=page&redirect_uri=https://'.BOT_HOST.'adm/?f=install_group&group_ids='.$_COOKIE['vkGroupId'].'&scope=messages&response_type=code&v=5.80');
    
}

else
{

require_once(dirname(__FILE__).'/template/top.php');
?>
<h1>Подключение бота к группе, шаг 1</h1>
<p>Чтобы подключить бота к группе/паблику у вас должны быть права администратора.</p>
<p>Подключение происходит в несколько этапов.<br/>
<strong>Право на управление сообществом необходимо для первичной настройки</strong> Callback API. Бот <strong>не хранит</strong> и не использует этот ключ после настройки, вы можете его удалить сразу после следующего шага.</p>
<form action="" method="post">
<p>Ссылка на группу, к которой будем подключать бота: <input type="text" name="vkGroupId" value=""></p>
<p><input type="submit" value="Подключить"></p>
</form><br><br>

<?php
require_once(dirname(__FILE__).'/template/bottom.php');

}



  ?>
