<?php

require_once('conf.php');
require_once('n.php');

require_once('../../_adm_/vk-campaign/core2duo.php');

$act_n=array_sum($active);
$send_n=array_sum($n);

mail ('aozol@intservis.ru','VK message stat',"Active: $act_n;
Sended: $send_n;
Error 14: $answer14");

$n_str='<?php

';

$sql=mysql_query("SELECT id FROM `".DBP."vk_account` WHERE active=1");
$i=0;
while (list($id)=mysql_fetch_array($sql)){

$n_str.='$id['.$i.']='.$id.'; $n['.$i.']=0; $active['.$i.']=1;

';

$i++;
if (is_file($id.'.tmp')) unlink($id.'.tmp');

}

$n_str.='$answer14=0;

?>';

$fh=fopen('n.php','w');
fwrite($fh,$n_str);
fclose($fh);

?>