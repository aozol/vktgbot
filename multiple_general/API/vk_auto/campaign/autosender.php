<?php
//exit; // временная приостановка

require_once('conf.php');
require('n.php');

ini_set('max_execution_time',$max_t);

//echo 'start<br/>';

$nn=sizeof($n);
if (array_sum($n)==$nn*$max_n)  exit; // если максимальное количество по всем - ничего не делаем

$n_act=0;
$nn_act=0;
for ($i=0;$i<$nn;$i++){
$n_act+=$active[$i]*$max_n;
$nn_act+=$active[$i]*$n[$i];
if (is_file($id[$i].'.tmp')) unlink($id[$i].'.tmp');
}

if ($n_act==$nn_act) exit; // если максимальное количество по активным - ничего не делаем

$auto=1;
require_once('conf.php');

for ($j=0;$j<$n_once;$j++){

require('handsender.php');
$sended[$j]=$sendmain;

$fh=fopen($sended[$j].'.tmp','w');
fwrite($fh,'1');
fclose($fh);
echo $sended[$j].'.tmp<br/>';
usleep($sleep_t);
}

for ($j=0;$j<$n_once;$j++) unlink($sended[$j].'.tmp');

?>