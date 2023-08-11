<?php
require_once('../conf.php');

require_once('login.php');
   
$page_title='Зачины - Шуткобот v2';
   
require_once('template/top.php');


if(isset($_GET['mlistId']))
	$mlistId=$_GET['mlistId'];
else
	$mlistId=0;

$sql=mysqli_query($dblink,"SELECT GROUP_CONCAT(vkGroupId) FROM `".DBP."shutkobotAdmin` WHERE vkId={$_COOKIE['vkId']}");
list($vkGroupIds)=mysqli_fetch_array($sql);

$sql=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId IN ({$vkGroupIds}) AND mlistId={$mlistId}");

if(!mysqli_num_rows($sql))
{
    echo '<p><strong>У Вас нет прав на управление Шуткоботом указанной группы</strong></p>';
    require_once('template/bottom.php');
    exit;
}

if (isset ($_POST['startTextNew']))  //Обработчик сохранения формы
{
   
   
   if($_POST['startTextNew'])
   {
      if($_POST['startDateNew']<=date('Y-m-d H:i:s'))
      {
        mysqli_query($dblink,"UPDATE `".DBP."starts` SET isActive=0 WHERE mlistId={$_POST['mlistId']}");
        mysqli_query($dblink,"INSERT INTO `".DBP."starts` (mlistId,startText,startDate,isActive) VALUES ({$_POST['mlistId']},'{$_POST['startTextNew']}','{$_POST['startDateNew']}',1)");
      }
      
      else
        mysqli_query($dblink,"INSERT INTO `".DBP."starts` (mlistId,startText,startDate,isActive) VALUES ({$_POST['mlistId']},'{$_POST['startTextNew']}','{$_POST['startDateNew']}',0)");
   }   
      
   
   foreach ($_POST['startText'] as $startId => $startText)
   {
        if($startText)
            mysqli_query($dblink,"UPDATE `".DBP."starts` SET startText='{$startText}', startDate='{$_POST['startDate'][$startId]}' WHERE startId={$startId}");
        else
            mysqli_query($dblink,"DELETE FROM `".DBP."starts` WHERE startId={$startId}");
   }
   echo '<p><strong style="color:red">Изменения внесены</strong></p>';
   
}

?>

<form action="" method="post">
<input type="hidden" name="mlistId" value="<? echo $mlistId; ?>" />
<p>Текст нового зачина: <input type="text" name="startTextNew" value="" /> дата запуска * <input type="text" name="startDateNew" value="<?php echo date('Y-m-d', time()+3600*24); ?> 00:00:00" />
<br>* если указать дату запуска раньше текущей, зачин автоматически активируется при сохранении. Если позже - то активируется после указанного времени (обновление ежедневно в 10:00 и 19:00)</p>
<table style="border: 1px solid;">
<tr><th>Текст **</th><th>Дата запуска</th><th>Статистика</th><th>Действия</th></tr>
<?php

$sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE mlistId={$mlistId} AND isActive=1");

list($startDateActive)=mysqli_fetch_array($sql);

$sql=mysqli_query($dblink,"SELECT startId,startText,startDate FROM `".DBP."starts` WHERE mlistId={$mlistId} AND (isActive=1 OR startDate>='{$startDateActive}') ORDER BY startDate DESC");
   
while(list($startId,$startText,$startDate)=mysqli_fetch_array($sql))
{
   $sql_f=mysqli_query($dblink,"SELECT COUNT(finishId) FROM `".DBP."finishes` WHERE startId={$startId}");
   list($nf)=mysqli_fetch_array($sql_f);
   
   $sql_f=mysqli_query($dblink,"SELECT COUNT(DISTINCT vkId) FROM `".DBP."finishes` WHERE startId={$startId}");
   list($nfu)=mysqli_fetch_array($sql_f);
   
   echo "<tr><td><input type=\"text\" name=\"startText[{$startId}]\" value=\"{$startText}\" /></td><td><input type=\"text\" name=\"startDate[{$startId}]\" value=\"{$startDate}\" /></td><td>{$nf} добивок от {$nfu} пользователей</td><td><a href=\"finishes.php?startId={$startId}\">Смотреть добивки</a></td></tr>";
}    
$sql=mysqli_query($dblink,"SELECT startId,startText,startDate FROM `".DBP."starts` WHERE mlistId={$mlistId} AND startDate<'{$startDateActive}' ORDER BY startDate DESC LIMIT 0,5");
   
while(list($startId,$startText,$startDate)=mysqli_fetch_array($sql))
{
   $sql_f=mysqli_query($dblink,"SELECT COUNT(finishId) FROM `".DBP."finishes` WHERE startId={$startId}");
   list($nf)=mysqli_fetch_array($sql_f);
   
   $sql_f=mysqli_query($dblink,"SELECT COUNT(DISTINCT vkId) FROM `".DBP."finishes` WHERE startId={$startId}");
   list($nfu)=mysqli_fetch_array($sql_f);
   
   echo "<tr><td><input type=\"text\" name=\"startText[{$startId}]\" value=\"{$startText}\" /></td><td><input type=\"text\" name=\"startDate[{$startId}]\" value=\"{$startDate}\" /></td><td>{$nf} добивок от {$nfu} пользователей</td><td><a href=\"finishes.php?startId={$startId}\">Смотреть добивки</a></td></tr>";
}   
?>
</table>
<p>** если вы хотите удалить зачин, сотрите его текст. Будьте внимательны и осторожны! Убедитесь, что удаляя зачин вы полностю понимаете, что делаете!</p>
<p><input type="submit" value="Сохранить изменения" /><br></p>
</form>

<p>Варианты, предложенные пользователями:</p>

<?

$sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts_new` WHERE mlistId={$mlistId} ORDER BY startId DESC LIMIT 0,15");
   
while(list($startText)=mysqli_fetch_array($sql))
   echo "<p>{$startText}</p>";


require_once('template/bottom.php');
?>
