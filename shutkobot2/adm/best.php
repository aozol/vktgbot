<?php
require_once('../conf.php');

require_once(GENERAL_DIR.'adm/functions.php');
require_once(GENERAL_DIR.'adm/login.php');
   
$page_title='Лучшие шутки - Бот с дополнениями шуткобота';
   
require_once('template/top.php');

require_once('../functions.php');

$n=10;

$mlists='';

if ($adm_info['root'])
    $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
else
    $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']})");
    


while (list($mlistId,$name)=mysqli_fetch_array($sql))
    $mlistName[$mlistId]=$name;

foreach ($mlistName as $k=>$v)
{
  
  $mlists.='<option value="'.$k.'">'.$v;
  
}


if (isset ($_POST['startDate']))  //Обработчик сохранения формы
{
   
   $sql=mysqli_query($dblink,"SELECT finishId,finishText,avgVote,medVote,startId FROM `".DBP."finishes` WHERE startId IN (SELECT startId FROM `".DBP."starts` WHERE startDate>='{$_POST['startDate']}' AND startDate<='{$_POST['finishDate']}' AND mlistId={$_POST['mlistId']}) ORDER BY avgVote DESC, medVote DESC LIMIT 0,{$_POST['n']}");

    echo '<table style="border: 1px solid;" ><th>Шутка</th><th>Среднее</th><th>Медиана</th>';

    while (list($finishId,$finishText,$avgVote,$medVote,$startId)=mysqli_fetch_array($sql))
    {
        
        $sql_start=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");
        list($startText)=mysqli_fetch_array($sql_start);
        
        echo "<tr><td>{$startText} {$finishText}</td><td>{$avgVote}</td><td>{$medVote}</td></tr>";

    }
    
    echo '</table>';
    
    $n=$_POST['n'];
    $mlists=str_replace('value="'.$_POST['mlistId'].'"','value="'.$_POST['mlistId'].'" selected',$mlists);
   
}



echo '<form action="" method="post">
С <input type="text" name="startDate" value="'.date("Y-m-01 00:00:00").'" /><br/>
По <input type="text" name="finishDate" value="'.date("Y-m-d 23:59:59").'" /><br/>
Количество <input type="text" name="n" value="'.$n.'" /><br/>
Список <select name="mlistId">'.$mlists.'</select><br/>
</table><p><input type="submit" value="Показать" /></p></form>';

require_once('template/bottom.php');
?>
