<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование редиректа с выполнением действий';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['finLink']))  //Обработчик сохранения формы
{
   
   if ($_POST['new'])
   {
      mysqli_query($dblink,"INSERT INTO `".DBP."redirects` (finLink,finDate,activationsN,actionPHP) VALUES ('".mysqli_escape_string($dblink,$_POST['finLink'])."','{$_POST['finDate']}',{$_POST['activationsN']},'".mysqli_escape_string($dblink,$_POST['actionPHP'])."')");
      $_GET['redirId']=mysqli_insert_id($dblink);
   }
   else
      mysqli_query($dblink,"UPDATE `".DBP."redirects` SET finLink='".mysqli_escape_string($dblink,$_POST['finLink'])."', finDate='{$_POST['finDate']}', activationsN={$_POST['activationsN']}, actionPHP='".mysqli_escape_string($dblink,$_POST['actionPHP'])."' WHERE  redirId='{$_GET['redirId']}'");
      
}

if (isset ($_GET['redirId']))
{
   $redirId=$_GET['redirId'];
   $new=0;
   
   $sql=mysqli_query($dblink,"SELECT finLink,finDate,activationsN,actionPHP FROM `".DBP."redirects` WHERE redirId='{$redirId}'");
   
   list($finLink,$finDate,$activationsN,$actionPHP)=mysqli_fetch_array($sql);
}

else
{
   $new=1;
   
   list($finLink,$finDate,$activationsN,$actionPHP)=array('',date('Y-m-d H:i:00'),0,'');
   
}

?>

<form action="" method="post">
<?php

if(!$new)
   echo '
   <input type="hidden" name="new" value="0" />
   <input type="hidden" name="redirId" value="'.$redirId.'" />';
else
{
   echo '<input type="hidden" name="new" value="1" />';
}
?>
<p>Ссылка, на которую переадресовывать: <input type="text" name="finLink" value="<?php if($finLink) echo $finLink; ?>" /></p>
<p>Дата окончания: <input type="text" name="finDate" value="<?php if($finDate) echo $finDate; else echo date('Y-m-d'); ?>" /></p>
<p>Максимальное количество активаций (0 - безлимитно): <input type="text" name="activationsN" value="<?php echo $activationsN; ?>" /></p>
<p>Действие (PHP): <textarea name="actionPHP" /><?php echo $actionPHP; ?></textarea></p>
<input type="submit" value="Сохранить" /><br></p>
</form>

<?




require_once(dirname(__FILE__).'/template/bottom.php');

?>
