<?php

require_once(dirname(__FILE__).'/conf.php');
require_once(dirname(__FILE__).'/../functions.php');
require_once(dirname(__FILE__).'/../functions.php');

require_once(dirname(__FILE__).'/../template/top.php');

if (isset($_POST)) if($_POST['domain'])
{
    $conf_str = file_get_contents ('default_conf.tpl');

    $conf_str = str_replace(array('%prefix%','%domain%','%dirname%','%vk_admin%','%def_buttons%','%def_bot_id%'),array($_POST['prefix'],$_POST['domain'],$_POST['dirname'],$_POST['vk_admin'],$_POST['def_buttons'],$_POST['def_bot_id']),$conf_str);
    
    $dir='../../../'.$_POST['dirname'];
    
    mkdir($dir,0755);
    mkdir($dir.'/adm/',0755);
    mkdir($dir.'/admin/',0755);
    mkdir($dir.'/logs/',0755);
    
    file_put_contents($dir.'/conf.php', $conf_str);
    file_put_contents($dir.'/chat_processor.php', file_get_contents ('chat_processor.tpl'));
    file_put_contents($dir.'/adm/index.php', file_get_contents ('adm_index.tpl'));
    file_put_contents($dir.'/admin/index.php', file_get_contents ('admin_index.tpl'));
    file_put_contents($dir.'/adm/reg.php', file_get_contents ('adm_reg.tpl'));
    
    $sql_query = str_replace(array('%prefix%','%adm_login%','%adm_pass%'),array($_POST['prefix'],$_POST['adm_login'],hash_pass($_POST['adm_pass'])),$sql_query);
    
    
    
    mysqli_multi_query($dblink, $sql_query);
    
    echo '<p><strong>Новый бот создан!</strong><br><a href="http://'.$_POST['domain'].'/telegram/'.$_POST['dirname'].'/adm/">Перейти</a></p>';
}

//echo 333;

?>

<h1>Создание нового бота</h1>

<form action="" method="post">
<p>Домен: <input type="text" name="domain" value="<?php echo $_SERVER['HTTP_HOST']; ?>"></p>
<p>Папка: <input type="text" name="dirname" value=""></p>
<p>Префикс таблиц: <input type="text" name="prefix" value=""></p><p>Id бота по умолчанию: <input type="text" name="def_bot_id" value=""></p>
<p>vkId админа: <input type="text" name="vk_admin" value="2204686"></p>

<p>Логин в адм.панель: <input type="text" name="adm_login" value="admin"></p>
<p>Пароль в адм.панель: <input type="password" name="adm_pass" value=""></p>
<p>Кнопки по умолчанию:</p>

<p><textarea name="def_buttons" style="width: 300px; height: 200px">$buttons_def[0][0][0]["payload"]="shutkobot_give_finish";
$buttons_def[0][0][1]='Добить';
$buttons_def[0][0][2]='primary';

$buttons_def[0][1][0]["payload"]="shutkobot_vote";
$buttons_def[0][1][1]='Голосовать';
$buttons_def[0][1][2]='primary';

$buttons_def[1][0][0]["payload"]="shutkobot_give_start";
$buttons_def[1][0][1]='Предложить зачин';
$buttons_def[1][0][2]='primary';

$buttons_def[2][0][0]["payload"]="shutkobot_best";
$buttons_def[2][0][1]='Смотреть лучшие';
$buttons_def[2][0][2]='primary';

$buttons_def[3][0][0]["payload"]="shutkobot_my";
$buttons_def[3][0][1]='Мои шутки';
$buttons_def[3][0][2]='primary';
        
$buttons_def[4][0][0]["payload"]="shutkobot_share";
$buttons_def[4][0][1]='Пригласить друга';
$buttons_def[4][0][2]='default';

$buttons_def[4][1][0]["payload"]="shutkobot_create";
$buttons_def[4][1][1]='Создать Шуткобота';
$buttons_def[4][1][2]='default';

$buttons_def[5][0][0]["payload"]="shutkobot_subscription";
$buttons_def[5][0][1]='Управление подпиской';
$buttons_def[5][0][2]='default';
//конец кнопок по умолчанию

$buttons_vote[0][0][0]["payload"]="shutkobot_vote:1";
$buttons_vote[0][0][1]='1';
$buttons_vote[0][0][2]='positive';

$buttons_vote[0][1][0]["payload"]="shutkobot_vote:3";
$buttons_vote[0][1][1]='3';
$buttons_vote[0][1][2]='positive';

$buttons_vote[0][2][0]["payload"]="shutkobot_vote:5";
$buttons_vote[0][2][1]='5';
$buttons_vote[0][2][2]='positive';

$buttons_vote[1][0][0]["payload"]="shutkobot_vote:8";
$buttons_vote[1][0][1]='8';
$buttons_vote[1][0][2]='positive';

$buttons_vote[1][1][0]["payload"]="shutkobot_vote:13";
$buttons_vote[1][1][1]='13';
$buttons_vote[1][1][2]='positive';

$buttons_my[0][0][0]["payload"]="shutkobot_my_best";
$buttons_my[0][0][1]="Топ по среднему";
$buttons_my[0][0][2]="positive"; //primary, positive, negative
            
$buttons_my[0][1][0]["payload"]="shutkobot_my_best:medVote";
$buttons_my[0][1][1]="Топ по медиане";
$buttons_my[0][1][2]="positive"; //primary, positive, negative
            
$buttons_my[1][0][0]["payload"]="shutkobot_my_best";
$buttons_my[1][0][1]="Топ предыдущего голосования";
$buttons_my[1][0][2]="positive"; //primary, positive, negative

$buttons_my[2][0][0]["payload"]="shutkobot_my_edit";
$buttons_my[2][0][1]="Редактировать отправленные добивки";
$buttons_my[2][0][2]="positive"; //primary, positive, negative</textarea>

<p><input type="submit" value="Создать"></p>
</form><br><br>

<?php require_once(dirname(__FILE__).'/../template/bottom.php'); ?>
