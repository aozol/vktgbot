<?php

require_once(dirname(__FILE__).'/conf.php');
require_once(dirname(__FILE__).'/../functions.php');
require_once(dirname(__FILE__).'/../functions.php');

require_once(dirname(__FILE__).'/../template/top.php');

if (isset($_POST)) if($_POST['domain'])
{
    if(!$_POST['def_bot_id'])
        $_POST['def_bot_id']=0;
    
    $conf_str = file_get_contents ('default_conf.tpl');

    $conf_str = str_replace(array('%prefix%','%domain%','%dirname%','%vk_admin%','%vk_support%','%def_buttons%','%def_bot_id%'),array($_POST['prefix'],$_POST['domain'],$_POST['dirname'],$_POST['vk_admin'],$_POST['vk_support'],$_POST['def_buttons'],$_POST['def_bot_id']),$conf_str);
    
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
    
    echo "<p>Запрос к БД:</p><p><textarea>{$sql_query}</textarea></p>";
    
    echo '<p><strong>Новый бот создан!</strong><br><a href="http://'.$_POST['domain'].'/telegram/'.$_POST['dirname'].'/adm/">Перейти</a></p>';
}

//echo 333;

?>

<h1>Создание нового бота</h1>

<form action="" method="post">
<p>Домен: <input type="text" name="domain" value="<?php echo $_SERVER['HTTP_HOST']; ?>"></p>
<p>Папка: <input type="text" name="dirname" value=""></p>
<p>Префикс таблиц: <input type="text" name="prefix" value=""></p><p>Id бота по умолчанию: <input type="text" name="def_bot_id" value=""></p>
<p>vkId админа: <input type="text" name="vk_admin" value="238009339"></p>
<p>vkId поддержки: <input type="text" name="vk_support" value="238009339"></p>

<p>Логин в адм.панель: <input type="text" name="adm_login" value="admin"></p>
<p>Пароль в адм.панель: <input type="password" name="adm_pass" value=""></p>
<p>Кнопки по умолчанию:</p>

<p><textarea name="def_buttons" style="width: 300px; height: 200px">$buttons_def[0][0][0]["payload"]="subscription";
$buttons_def[0][0][1]='Управление подпиской';
$buttons_def[0][0][2]='default';

//кнопки по умолчанию
        
$buttons_def[0][0][0]["payload"]="subscription";
$buttons_def[0][0][1]='Управление подпиской';
$buttons_def[0][0][2]='default';

$buttons_def[0][1][0]["payload"]="start";
$buttons_def[0][1][1]='Перезапуск бота';
$buttons_def[0][1][2]='default';</textarea>

<p><input type="submit" value="Создать"></p>
</form><br><br>

<?php require_once(dirname(__FILE__).'/../template/bottom.php'); ?>