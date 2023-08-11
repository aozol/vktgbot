<?php



function handsender(){

require_once('conf.php');
require('n.php');

$nn=sizeof($n);
if (array_sum($n)==$nn*$max_n)  exit; // если максимальное количество по всем - ничего не делаем

$n_act=0;
$nn_act=0;
for ($i=0;$i<$nn;$i++){
$n_act+=$active[$i]*$max_n;
$nn_act+=$active[$i]*$n[$i];
}

if ($n_act==$nn_act) exit; // если максимальное количество по активным - ничего не делать

$conf_str=file_get_contents('conf.php');
$n_str=file_get_contents('n.php');

$y=0;
while($y<2){
$i=rand(0,$nn-1);
if (($active[$i]) AND ($n[$i]<$max_n) AND (!is_file($id[$i].'.tmp'))){
$_GET['sendmain']=$id[$i];
require('../_adm_/vk-campaign/send.php');

if ($responce>0) {//если ошибок нет
$y=2; //останавливаем попытки отправки, т.к. все ок
$n_str=str_replace('$n['.$i.']='.$n[$i].';','$n['.$i.']='.($n[$i]+1).';',$n_str);
}
if ($responce<-1){//ошибка на стороне вк - делаем еще одну попытку
$y++;

require('n.php');
}

}



if (($responce=='-14') OR ($responce=='-5') OR ($responce=='-0.1')){
$n_str=str_replace('$active['.$i.']=1;','$active['.$i.']=0;',$n_str);
$n_str=str_replace('$answer0='.$answer0.';','$answer0='.($answer0+1).';',$n_str);
}
}


$fh=fopen('conf.php','w');
fwrite($fh,$conf_str);
fclose($fh);

$fh=fopen('n.php','w');
fwrite($fh,$n_str);
fclose($fh);

return $id[$i];
}

if (!isset($auto)) echo handsender();
?>