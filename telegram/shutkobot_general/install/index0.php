<?php

require_once(dirname(__FILE__).'/conf.php');
require_once(dirname(__FILE__).'/../functions.php');
require_once(dirname(__FILE__).'/../functions.php');

require_once(dirname(__FILE__).'/../template/top.php');

if (isset($_POST))
{
    $conf_str = file_get_contents ('default_conf.tpl');

    $conf_str = str_replace(array('%prefix%','%domain%','%dirname%','%vk_admin%','%def_buttons%'),array($_POST['prefix'],$_POST['domain'],$_POST['dirname'],$_POST['vk_admin'],$_POST['def_buttons']),$conf_str);
    
    $dir='../../../'.$_POST['dirname'];
    
    mkdir($dir,0755);
    mkdir($dir.'/adm/',0755);
    mkdir($dir.'/logs/',0755);
    
    file_put_contents($dir.'/conf.php', $conf_str);
    file_put_contents($dir.'/chat_processor.php', file_get_contents ('chat_processor.tpl'));
    file_put_contents($dir.'/adm/index.php', file_get_contents ('adm_index.tpl'));
    file_put_contents($dir.'/adm/reg.php', file_get_contents ('adm_reg.tpl'));
    
    $sql_query = str_replace(array('%prefix%','%adm_login%','%adm_pass%'),array($_POST['prefix'],$_POST['adm_login'],hash_pass($_POST['adm_pass'])),$sql_query);
    
    
    
    mysqli_multi_query($dblink, $sql_query);
    
    echo '<p><strong>Новый бот создан!</strong><br><a href="http://'.$_POST['domain'].'/'.$_POST['dirname'].'/adm/">Перейти</a></p>';
}

//echo 333;

?>

<h1>Создание нового бота</h1>

<form action="" method="post">
<p>Домен: <input type="text" name="domain" value="<?php echo $_SERVER['HTTP_HOST']; ?>"></p>
<p>Папка: <input type="text" name="dirname" value=""></p>
<p>Префикс таблиц: <input type="text" name="prefix" value=""></p>
<p>vkId админа: <input type="text" name="vk_admin" value=""></p>

<p>Логин в адм.панель: <input type="text" name="adm_login" value="admin"></p>
<p>Пароль в адм.панель: <input type="password" name="adm_pass" value=""></p>
<p>Кнопки по умолчанию:</p>

<p><textarea name="def_buttons" style="width: 300px; height: 200px">$buttons_def[0][0][0]["payload"]="subscription";
$buttons_def[0][0][1]='Управление подпиской';
$buttons_def[0][0][2]='default';</textarea>

<p><input type="submit" value="Создать"></p>
</form><br><br>

<?php require_once(dirname(__FILE__).'/../template/bottom.php'); ?>
