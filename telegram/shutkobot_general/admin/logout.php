<?

require_once(dirname(__FILE__).'/../conf.php');
require_once(GENERAL_DIR.'/adm/functions.php');
require_once(dirname(__FILE__).'/../functions.php');
//require_once(dirname(__FILE__).'/../adm/functions.php');

setcookie("vkId",$_GET['uid'],time()-3*24*3600);
setcookie("hash",$_GET['hash'],time()-3*24*3600);
?>

Вы вышли из панели администратора!<br>
<a href="index.php">Войти снова</a>
