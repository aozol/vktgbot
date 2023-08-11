<?php

$conf_str=file_get_contents('conf.php');
$n_str=file_get_contents('n.php');

$y=0;
while($y<2){
$i=rand(0,$nn-1);
if (($active[$i]) AND ($n[$i]<$max_n) AND (!is_file($id[$i].'.tmp'))){
$sendmain=$id[$i];

require($path.'send.php');

if ($result>0) {//если ошибок нет
$y=2; //останавливаем попытки отправки, т.к. все ок
$n_str=str_replace('$n['.$i.']='.$n[$i].';','$n['.$i.']='.($n[$i]+1).';',$n_str);
}
if ($result<-1){//ошибка на стороне вк - делаем еще одну попытку
$y++;

}

}


if (($result=='-14') OR ($result=='-5') OR ($result=='-0.1')){
$n_str=str_replace('$active['.$i.']=1;','$active['.$i.']=0;',$n_str);
$n_str=str_replace('$answer14='.$answer14.';','$answer14='.($answer14+1).';',$n_str);
}
}


$fh=fopen('conf.php','w');
fwrite($fh,$conf_str);
fclose($fh);

$fh=fopen('n.php','w');
fwrite($fh,$n_str);
fclose($fh);

echo 'Result for account '.$sendmain.': '.$result.'<br/>';



?>