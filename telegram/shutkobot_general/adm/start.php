<?php
require_once('../conf.php');

require_once('functions.php');
require_once('login.php');
   
$page_title='Зачины - Шуткобот v2';
   
require_once('template/top.php');


if (isset ($_POST['startTextNew']))  //Обработчик сохранения формы
{
   
   
   if($_POST['startTextNew'])
      mysqli_query($dblink,"INSERT INTO `".DBP."starts` (mlistId,startText,startDate,isActive) VALUES ({$_POST['mlistId']},'{$_POST['startTextNew']}','{$_POST['startDateNew']}',0)");
      
      
   
   foreach ($_POST['startText'] as $startId => $startText)
      mysqli_query($dblink,"UPDATE `".DBP."starts` SET startText='{$startText}', startDate='{$_POST['startDate'][$startId]}' WHERE startId={$startId}");
   
   echo '<p>Изменения внесены</p>';
   
}

if(isset($_GET['mlistId']))
	$mlistId=$_GET['mlistId'];
else
	$mlistId=0;
?>

<form action="" method="post">
<input type="hidden" name="mlistId" value="<? echo $mlistId; ?>" />
<p>Текст нового зачина: <input type="text" name="startTextNew" value="" /> дата запуска <input type="text" name="startDateNew" value="<?php echo date('Y-m-d', time()+3600*24); ?> 00:00:00" /></p>
<table style="border: 1px solid;">
<tr><th>Текст</th><th>Дата запуска</th></tr>
<?php

$sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE mlistId={$mlistId} AND isActive=1");

list($startDateActive)=mysqli_fetch_array($sql);

$sql=mysqli_query($dblink,"SELECT startId,startText,startDate FROM `".DBP."starts` WHERE mlistId={$mlistId} AND (isActive=1 OR startDate>='{$startDateActive}') ORDER BY startDate DESC");
   
while(list($startId,$startText,$startDate)=mysqli_fetch_array($sql))
   echo "<tr><td><input type=\"text\" name=\"startText[{$startId}]\" value=\"{$startText}\" /></td><td><input type=\"text\" name=\"startDate[{$startId}]\" value=\"{$startDate}\" /> <a href=\"finishes.php?startId={$startId}\">Смотреть добивки</a></td></tr>";
   
$sql=mysqli_query($dblink,"SELECT startId,startText,startDate FROM `".DBP."starts` WHERE mlistId={$mlistId} AND startDate<'{$startDateActive}' ORDER BY startDate DESC LIMIT 0,5");
   
while(list($startId,$startText,$startDate)=mysqli_fetch_array($sql))
   echo "<tr><td><input type=\"text\" name=\"startText[{$startId}]\" value=\"{$startText}\" /></td><td><input type=\"text\" name=\"startDate[{$startId}]\" value=\"{$startDate}\" /> <a href=\"finishes.php?startId={$startId}\">Смотреть добивки</a></td></tr>";

?>
</table>

<input type="submit" value="Сохранить изменения" /><br></p>
</form>

<p>Варианты, предложенные пользователями:</p>

<?

$sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts_new` WHERE mlistId={$mlistId} ORDER BY startId DESC LIMIT 0,15");
   
while(list($startText)=mysqli_fetch_array($sql))
   echo "<p>{$startText}</p>";


require_once(GENERAL_DIR.'adm/template/bottom.php');
?>
