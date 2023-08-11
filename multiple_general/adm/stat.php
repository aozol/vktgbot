
<?php

require_once('functions.php');

$sql=mysqli_query($dblink,"SELECT listId,count(listId) FROM `campleader_int_db` WHERE unsub=0 GROUP BY listId");

echo '<p>Статистика по количеству действующих подписчиков</p><p>';

while (list($listId,$n)=mysqli_fetch_array($sql))
   echo $listname[$listId].': '.$n.'<br>';
   
echo '</p>';

?>
