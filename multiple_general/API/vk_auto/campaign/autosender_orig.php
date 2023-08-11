<?php
//exit; // временная приостановка

ini_set('max_execution_time',$max_t);

echo 'start<br/>';
$auto=1;
require_once('conf.php');

for ($j=0;$j<$n_once;$j++){

require('handsender.php');
$sended[$j]=$sendmain;

$fh=fopen($sended[$j].'.tmp','w');
fwrite($fh,'1');
fclose($fh);
echo $sended[$j].'.tmp<br/>';
sleep(rand($min_sleep,$max_sleep));
}

for ($j=0;$j<$n_once;$j++) unlink($sended[$j].'.tmp');

?>