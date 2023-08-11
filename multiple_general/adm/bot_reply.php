<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование шаблонов реакций бота';

require_once(dirname(__FILE__).'/template/top.php');

$gId=0;

if (isset ($_GET['gId']))
  $gId=$_GET['gId'];
  
if (isset ($_POST['payloadText']))  //Обработчик сохранения формы
{
   
   
   mysqli_query($dblink,"UPDATE `".DBP."vkApi` SET vkGroupPHP='".mysqli_real_escape_string($dblink,$_POST['vkGroupPHP'])."' WHERE  vkGroupId={$gId}");
   
   mysqli_query($dblink,"DELETE FROM `".DBP."botReply` WHERE vkGroupId={$gId}");
   
   foreach ($_POST['payloadText'] as $i=>$payloadText)
      if ($payloadText)
         mysqli_query($dblink,"INSERT INTO `".DBP."botReply` (vkGroupId,payloadText,replyText,php) VALUES ({$gId},'".mysqli_real_escape_string($dblink,$payloadText)."','".mysqli_real_escape_string($dblink,$_POST['replyText'][$i])."','".mysqli_real_escape_string($dblink,$_POST['php'][$i])."')");
      
}  

if (!$gId)
{
   echo 'No group specified. Error!';
   
   require_once('template/bottom.php');
   
   exit;
}



   
$fn=0;

$params['group_id'] = $gId;
$result=_vkApi_call('groups.getById', $params);

echo "
<h1>Редактирование алгоритмов ответа для группы \"<a href=\"https://vk.com/club{$gId}\" target=\"_vk\">{$result[0]['name']}</a>\"</h1>";

if ($adm_info['root'])
    echo '<p>Используемые переменные:<br>
Шаблон кнопки бота<br>
$buttons[0][0][0]["payload"]="command";<br>
$buttons[0][0][1]="Описание на кнопке";<br>
$buttons[0][0][2]="default"; //primary, positive, negative<br><br>

$data - объект данных от ВК<br>
$vkId - id пользователя, отправившего сообщение<br>
$currentGroupId - id текущей группы<br>
$incommingText - текст входящего сообщения<br>
$userState - переменная для сохранения текущего состояния пользователя в базе<br>
$payloadText - текст команды<br>
$replyText - ответа (изначально грузится из базы)<br>
$attachments - вложения к сообщению (массив)
<br/><br/>';

echo '
Шаблоны для сообщений:<br/>
%name% - имя человека (как указано в вк)<br/>
%last_name% - фамилия человека (как указано в вк)<br/>
%full_name% - полное имя человека (строится на основе имени из вк: Маша -> Мария)<br/>
%t_hi% - Доброе утро/Добрый день/Добрый вечер/Доброй ночи - в зависимости от текущего времени суток<br/>
%t_hi_small% - доброе утро/добрый день/добрый вечер/доброй ночи - с маленькой буквы, в зависимости от текущего времени суток<br/>
{женское|мужское} - различные варианты в зависимости от пола. Поледовательность именно такая!

</p>

<form action="" method="post">';

if ($adm_info['root'])
{
    $sql=mysqli_query($dblink,"SELECT vkGroupPHP FROM `".DBP."vkApi` WHERE vkGroupId={$gId}");
    list($vkGroupPHP)=mysqli_fetch_array($sql);
    echo "<p>В этом поле можно написать общий код, который выполнится перед всеми командами в группе: <br/><textarea name=\"vkGroupPHP\">{$vkGroupPHP}</textarea></p><br/><br/>";
}
echo "
<div id=\"parentId\">";

$sql=mysqli_query($dblink,"SELECT payloadText,replyText,php FROM `".DBP."botReply` WHERE vkGroupId={$gId}");
   
while (list($payloadText,$replyText,$php)=mysqli_fetch_array($sql))
{
    if ($adm_info['root'])
        echo "<nobr> <p> payloadText: <input type=\"text\" name=\"payloadText[{$fn}]\" value=\"{$payloadText}\" /> replyText: <textarea name=\"replyText[{$fn}]\">{$replyText}</textarea> php: <textarea name=\"php[{$fn}]\">{$php}</textarea><a style=\"color:red;\" onclick=\"return deleteField(this)\" href=\"#\">[—]</a> <a style=\"color:green;\" onclick=\"return addField()\" href=\"#\">[+]</a></p></nobr>";
    else
        echo "<nobr> <p><input type=\"hidden\" name=\"payloadText[{$fn}]\" value=\"{$payloadText}\" />Ответ бота для кнопки \"{$payloadText}\": <textarea name=\"replyText[{$fn}]\">{$replyText}</textarea> <input type=\"hidden\" name=\"php[{$fn}]\" value=\"".htmlspecialchars($php,ENT_QUOTES)."\" /></p></nobr>";
    
    $fn++;
}
if ($adm_info['root'])
    echo "<nobr> <p> payloadText: <input type=\"text\" name=\"payloadText[{$fn}]\" value=\"\" /> replyText: <textarea name=\"replyText[{$fn}]\"></textarea> php: <textarea name=\"php[{$fn}]\"></textarea><a style=\"color:red;\" onclick=\"return deleteField(this)\" href=\"#\">[—]</a> <a style=\"color:green;\" onclick=\"return addField()\" href=\"#\">[+]</a></p></nobr>";

    echo "
</div>
<p><input type=\"submit\" value=\"Сохранить\" /></p>
</form>";

$fn++;

if ($adm_info['root'])
{
?>

<script>
var countOfFields = <?php echo $fn; ?>; // Текущее число полей
var curFieldNameId = <?php echo $fn; ?>; // Уникальное значение для атрибута name
var maxFieldLimit = 25; // Максимальное число возможных полей
function deleteField(a) {
  if (countOfFields > 1)
  {
 // Получаем доступ к ДИВу, содержащему поле
 var contDiv = a.parentNode;
 // Удаляем этот ДИВ из DOM-дерева
 contDiv.parentNode.removeChild(contDiv);
 // Уменьшаем значение текущего числа полей
 countOfFields--;
 }
 // Возвращаем false, чтобы не было перехода по сслыке
 return false;
}
function addField() {
 // Проверяем, не достигло ли число полей максимума
 if (countOfFields >= maxFieldLimit) {
 alert("Число полей достигло своего максимума = " + maxFieldLimit);
 return false;
 }
 // Увеличиваем текущее значение числа полей
 countOfFields++;
 // Увеличиваем ID
 curFieldNameId++;
 // Создаем элемент ДИВ
 var div = document.createElement("div");
 // Добавляем HTML-контент с пом. свойства innerHTML
 div.innerHTML = "<nobr> <p> payloadText: <input type=\"text\" name=\"payloadText[" + curFieldNameId + "]\" value=\"\" /> replyText: <textarea name=\"replyText[" + curFieldNameId + "]\"></textarea> php: <textarea name=\"php[" + curFieldNameId + "]\"></textarea><a style=\"color:red;\" onclick=\"return deleteField(this)\" href=\"#\">[—]</a> <a style=\"color:green;\" onclick=\"return addField()\" href=\"#\">[+]</a></p></nobr>";
 // Добавляем новый узел в конец списка полей
 document.getElementById("parentId").appendChild(div);
 // Возвращаем false, чтобы не было перехода по сслыке
 return false;
}
</script>

<?
}



require_once(dirname(__FILE__).'template/bottom.php');

?>
