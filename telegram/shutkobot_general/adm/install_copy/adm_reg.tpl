<?php
/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo 'start';


require_once ('../conf.php');

require_once (GENERAL_DIR.'adm/functions.php');


mysqli_query($dblink,'INSERT INTO `'.DBP.'admin` (`login`,`pass`) VALUES ("'.$_GET['l'].'","'.hash_pass($_GET['p']).'")');

$sql=mysqli_query($dblink,"SELECT * FROM ".DBP."admin WHERE login='".$_GET['l']."'");
$from_admin=mysqli_fetch_assoc($sql);

echo 'Администратор зарегистрирован под номером '.$from_admin['id'];
//*/

?>
