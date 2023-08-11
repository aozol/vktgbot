

<?php


//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

require_once(GENERAL_DIR.'functions.php');

//require_once(GENERAL_DIR.'update_MySQL.php');


function hash_pass($pass){

return sha1(md5(md5($pass).'VkBotBotMyBot'));

}

$sql=mysqli_query($dblink,"SELECT token,confirmToken,secret FROM `".DBP."vkApi`");
   
list($token,$confirmToken,$secret)=mysqli_fetch_array($sql);



?>
