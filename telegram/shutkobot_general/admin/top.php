<?php
require_once('../conf.php');

require_once('login.php');
   
$page_title='Топ шуток - Шуткобот v2';
   
require_once('template/top.php');

if (isset ($_POST['startDate']))  //изменение даты по умолчанию
{
    $startDate=$_POST['startDate'];
    $finishDate=$_POST['finishDate'];
    if ($_POST['votes'])
        $votes=1;
    else
        $votes=0;
}

else
{
    $startDate=date('Y-m-01');
    $finishDate=date('Y-m-d');
    $votes=1;
}


?>
<form action="" method="post">
<p>Дата начала: <input type="text" name="startDate" value="<?php echo $startDate; ?>" /> дата окончания:  <input type="text" name="finishDate" value="<?php echo $finishDate; ?>" /> показать оценки:  <input type="checkbox" name="votes" value="1" <?php if ($votes) echo "checked"; ?> /></p>

<p><input type="submit" value="Посмотреть" /><br></p>
</form>

<?php

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

$sql=mysqli_query($dblink,"SELECT startId,startText,startDate FROM `".DBP."starts` WHERE startDate>='{$startDate}' AND startDate<='{$finishDate}' AND (isActive=0) AND mlistId={$mlistId} ORDER BY startDate DESC");



echo "<h1>Лучшие шутки с {$startDate} по {$finishDate}<br>&nbsp;</h1>";

echo "<h2>Лидеры голосований</h2>";

$joke=array();
while(list($startId,$startText,$startDate)=mysqli_fetch_array($sql))
{
   $sql_f=mysqli_query($dblink,"SELECT finishId,finishText,avgVote,medVote FROM `".DBP."finishes` WHERE startId={$startId} ORDER BY avgVote DESC");
   for($i=0;$i<3;$i++)
   {
    if(list($finishId,$finishText,$avgVote,$medVote) = mysqli_fetch_array($sql_f))
    {
        $joke[$finishId]['text']=$startText.' '.$finishText;
        $joke[$finishId]['avgVote']=$avgVote;
        $joke[$finishId]['medVote']=$medVote;
        $vote[$finishId]=$avgVote;
        
        if ($votes)
            echo "<p>{$joke[$finishId]['text']} (средняя оценка {$joke[$finishId]['avgVote']}, медиана {$joke[$finishId]['medVote']})</p>";
        else
            echo "<p>{$joke[$finishId]['text']}</p>";
    }
   }
   
   echo '<p>&nbsp;<br></p>';
   
}

echo "<h2>Топ-30 из всех</h2>";

arsort($vote);
$n=1;
foreach ($vote as $finishId=>$v)
{
    if ($n>30) break;
    
    if ($votes)
        echo "<p>{$n}. {$joke[$finishId]['text']} (средняя оценка {$joke[$finishId]['avgVote']}, медиана {$joke[$finishId]['medVote']})</p>";
    else
        echo "<p>{$n}. {$joke[$finishId]['text']}</p>";
    
    $n++;
}


require_once('template/bottom.php');
?>
