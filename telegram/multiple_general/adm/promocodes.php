<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование промокода';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['promoCode']))  //Обработчик сохранения формы
{
   
   $_GET['promoCode']=$_POST['promoCode'];
   $promoCode=$_GET['promoCode'];
   $_GET['finDate']=$_POST['exfinDate'];
   
   if ($_POST['new'])
      mysqli_query($dblink,"INSERT INTO `".DBP."promocodes` (promoCode,finDate,activationsN,actionPHP) VALUES ('".mysqli_escape_string($dblink,$_POST['promoCode'])."','{$_POST['finDate']}',{$_POST['activationsN']},'".mysqli_escape_string($dblink,$_POST['actionPHP'])."')");
   else
      mysqli_query($dblink,"UPDATE `".DBP."promocodes` SET promoCode='".mysqli_escape_string($dblink,$_POST['promoCode'])."', finDate='{$_POST['finDate']}', activationsN={$_POST['activationsN']}, actionPHP='".mysqli_escape_string($dblink,$_POST['actionPHP'])."' WHERE  promoCode='{$_POST['promoCode']}' AND  finDate='{$_GET['finDate']}'");
      
      
   
      
   $_GET['finDate']=$_POST['finDate'];
}

if (isset ($_GET['promoCode']))
{
   $promoCode=$_GET['promoCode'];
   $finDate=$_GET['finDate'];
   $new=0;
   
   $sql=mysqli_query($dblink,"SELECT activationsN,actionPHP FROM `".DBP."promocodes` WHERE promoCode='{$promoCode}' AND finDate='{$finDate}'");
   
   list($activationsN,$actionPHP)=mysqli_fetch_array($sql);
}

else
{
   $new=1;
   $finDate=date('Y-m-d H:i:00');
   
   list($activationsN,$actionPHP)=array(0,'');
   
}

?>

<form action="" method="post">
<?php

if(!$new)
   echo '<p>Промокод: '.$promoCode.'</p>
   <input type="hidden" name="new" value="0" />
   <input type="hidden" name="promoCode" value="'.$promoCode.'" />
   <input type="hidden" name="exfinDate" value="'.$finDate.'" />';
else
{
   echo '<input type="hidden" name="new" value="1" />
   <p>Промокод: <input type="text" name="promoCode" value="" /></p>';
}
?>
<p>Дата окончания: <input type="text" name="finDate" value="<?php if($finDate) echo $finDate; else echo date('Y-m-d'); ?>" /></p>
<p>Максимальное количество активаций (0 - безлимитно): <input type="text" name="activationsN" value="<?php echo $activationsN; ?>" /></p>
<p>Действие (PHP): <textarea name="actionPHP" /><?php echo $actionPHP; ?></textarea></p>
<input type="submit" value="Сохранить" /><br></p>
</form>

<?




require_once(dirname(__FILE__).'template/bottom.php');

?>
