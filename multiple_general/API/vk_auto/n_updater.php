<?php

require_once('conf.php');
require_once('n.php');

require_once('../_adm_/vk-send2/core2duo.php');



//mail ('aozol@intservis.ru','JC autosub stat',"Active: $act_n;
//Subscribed: $sub_n-$answer0;");

$n_str='<?php

';

$sql=mysql_query("SELECT id FROM `".DBP."vk_account` WHERE active=1");
$i=0;
while (list($id)=mysql_fetch_array($sql)){

$n_str.='$id['.$i.']='.$id.'; $n['.$i.']=0; $active['.$i.']=1;

';

$i++;

}

$n_str.='$answer0=0;

?>';

$fh=fopen('n.php','w');
fwrite($fh,$n_str);
fclose($fh);

?>