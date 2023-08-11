<?php

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/../adm/functions.php');
require_once(dirname(__FILE__).'/../functions.php');

setcookie("vkId",$_GET['uid'],time()-3600);
setcookie("hash",$_GET['hash'],time()-3600);

if(isset($bot_title))
    $title="Вы вышли | {$bot_title}";
else
    $title="Вы вышли | Бот для vk.com";

    
require_once(dirname(__FILE__).'/template/top.php');
?>

Вы вышли из панели администратора!<br>
<a href="index.php">Войти снова</a>

<?php
require_once(dirname(__FILE__).'/template/bottom.php');
?>
