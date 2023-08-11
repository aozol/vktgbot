<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование данных API VK для группы';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['gId']))  //Обработчик сохранения формы
{
   
   $_GET['gId']=$_POST['gId'];
   $gId=$_GET['gId'];
   
   mysqli_query($dblink,"DELETE FROM `".DBP."vkApi` WHERE vkGroupId={$gId}");
   mysqli_query($dblink,"INSERT INTO `".DBP."vkApi` (vkGroupId,token,confirmToken,secret) VALUES ({$gId},'{$_POST['token']}','{$_POST['confirmToken']}','{$_POST['secret']}')");
   
   echo "INSERT INTO `".DBP."vkApi` (vkGroupId,token,confirmToken,secret) VALUES ({$gId},'{$_POST['token']}','{$_POST['confirmToken']}','{$_POST['secret']}')";
      
}

if (isset ($_GET['gId']))
{
   $gId=$_GET['gId'];
   
   $sql=mysqli_query($dblink,"SELECT token,confirmToken,secret FROM `".DBP."vkApi` WHERE vkGroupId={$gId}");
   
   list($token,$confirmToken,$secret)=mysqli_fetch_array($sql);
}

else
{
   $gId=0;
   
   list($token,$confirmToken,$secret)=array('','','');
   
}

?>

<form action="" method="post">
<p>vkGroupId: <input type="text" name="gId" value="<?php if($gId) echo $gId; ?>" /><br>
Token: <input type="text" name="token" value="<?php echo $token; ?>" /><br>
ConfirmationToken: <input type="text" name="confirmToken" value="<?php echo $confirmToken; ?>" /><br>
SecretKey: <input type="text" name="secret" value="<?php echo $secret; ?>" /><br>
<input type="submit" value="Сохранить" /><br></p>
</form>

<?

if ($token)
    echo '<p><a href="https://api.telegram.org/bot'.$token.'/setwebhook?url=https://'.BOT_HOST.'/chat_processor.php?botId='.$gId.'">Установить вебхук</a></p>';


require_once(dirname(__FILE__).'/template/bottom.php');

?>
