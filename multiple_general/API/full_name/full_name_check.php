<?php

require_once (dirname(__FILE__).'/conf.php');
  if (!mysql_connect(DB_SERVER, DB_LOGIN, DB_PASS)) { echo 'Ошибка подключения к базе'; exit; }
  mysql_query("SET NAMES 'utf8'"); //на всякий случай, бывают проблемы с русскими буквами
  mysql_select_db(DB_NAME);
  
if (isset ($_POST["checked"])){

echo '<p>start saving...</p>';

$short=$_POST['short'];
$full=$_POST['full'];
$sex=$_POST['sex'];
$n=sizeof($short);

for ($i=0;$i<$n;$i++){
mysql_query("UPDATE `full_names` SET checked=1, full='".$full[$i]."' WHERE short='".$short[$i]."' AND  sex='".$sex[$i]."'");
echo ("UPDATE `full_names` SET checked=1, full='".$full[$i]."' WHERE short='".$short[$i]."' AND  sex='".$sex[$i]."'").'<br/>';
}


}

$sexlist='<select name="sex[]">
<option value="0">Не определен
<option value="1">Женский
<option value="2">Мужской
</select>';
  

$sql=mysql_query("SELECT short,full,sex FROM `full_names` WHERE checked=0");
if (mysql_num_rows($sql)){
echo '<form action="" method="post">
<input type="hidden" name="checked" value="1" />';

while ($res=mysql_fetch_assoc($sql)){
echo '<p><input type="hidden" name="short[]" value="'.$res['short'].'" />Краткое "'.$res['short'].'" - полное <input type="text" name="full[]" value="'.$res['full'].'" />, пол:'.str_replace('"'.$res['sex'].'"','"'.$res['sex'].'" selected',$sexlist).'</p>';
}

echo '<input type="submit" value="Сохранить" />
</form>';
}

else echo '<p>На данный момент имен для проверки нет</p>';

?>
