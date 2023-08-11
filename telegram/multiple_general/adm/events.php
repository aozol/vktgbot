<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование мероприятия';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_GET['eventId']))
{
   $eventId=$_GET['eventId'];
   $new=0;
}

if (isset ($_POST['eventId']))  //Обработчик сохранения формы
{
   
   $_GET['eventId']=$_POST['eventId'];
   $eventId=$_GET['eventId'];

   
   if (($_POST['new']) OR (isset($_GET['copy'])))
   {
      mysqli_query($dblink,"INSERT INTO `".DBP."events` (eventName,eventStart,eventStartTime,eventFinish,eventFinishTime,eventPreregStart,eventRegStart,eventPreregInfo,eventPreregPHP,eventRegInfo,eventRegPHP,vkGroupId) VALUES ('".mysqli_escape_string($dblink,$_POST['eventName'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventStart'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventStartTime'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventFinish'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventFinishTime'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventPreregStart'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventRegStart'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventPreregInfo'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventPreregPHP'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventRegInfo'])."',
                   '".mysqli_escape_string($dblink,$_POST['eventRegPHP'])."',{$_POST['vkGroupId']})");

      $_POST['eventId']=mysqli_insert_id($dblink);
      $_GET['eventId']=$_POST['eventId'];
      $eventId=$_GET['eventId'];
   }
   else
      mysqli_query($dblink,"UPDATE `".DBP."events` SET
                  eventName='".mysqli_escape_string($dblink,$_POST['eventName'])."',
                   eventStart='".mysqli_escape_string($dblink,$_POST['eventStart'])."',
                   eventStartTime='".mysqli_escape_string($dblink,$_POST['eventStartTime'])."',
                   eventFinish='".mysqli_escape_string($dblink,$_POST['eventFinish'])."',
                   eventFinishTime='".mysqli_escape_string($dblink,$_POST['eventFinishTime'])."',
                   eventPreregStart='".mysqli_escape_string($dblink,$_POST['eventPreregStart'])."',
                   eventRegStart='".mysqli_escape_string($dblink,$_POST['eventRegStart'])."',
                   eventPreregInfo='".mysqli_escape_string($dblink,$_POST['eventPreregInfo'])."',
                   eventPreregPHP='".mysqli_escape_string($dblink,$_POST['eventPreregPHP'])."',
                   eventRegInfo='".mysqli_escape_string($dblink,$_POST['eventRegInfo'])."',
                   eventRegPHP='".mysqli_escape_string($dblink,$_POST['eventRegPHP'])."',
                   vkGroupId=".mysqli_escape_string($dblink,$_POST['vkGroupId'])."
                   WHERE eventId={$eventId}");
      
      
  
   if (($_POST['roleChange']) OR (isset($_GET['copy'])))
   {
      mysqli_query($dblink,"DELETE FROM `".DBP."events_roles` WHERE eventId={$eventId}");
      

      
      foreach ($_POST['roleId'] as $i => $roleId)
      {
         if ($roleId)
            mysqli_query($dblink,"INSERT INTO `".DBP."events_roles` (eventId,roleId,roleName,roleMessage,rolePHP) VALUES ({$eventId},{$roleId},
                   '".mysqli_escape_string($dblink,$_POST['roleName'][$i])."',
                   '".mysqli_escape_string($dblink,$_POST['roleMessage'][$i])."',
                   '".mysqli_escape_string($dblink,$_POST['rolePHP'][$i])."')");
      
      }
   
   }


}

if (isset ($_GET['eventId']))
{
   $eventId=$_GET['eventId'];
   $new=0;
   
   $sql=mysqli_query($dblink,"SELECT eventName,eventStart,eventStartTime,eventFinish,eventFinishTime,eventPreregStart,eventRegStart,eventPreregInfo,eventPreregPHP,eventRegInfo,eventRegPHP,vkGroupId FROM `".DBP."events` WHERE eventId={$eventId}");
   
   list($eventName,$eventStart,$eventStartTime,$eventFinish,$eventFinishTime,$eventPreregStart,$eventRegStart,$eventPreregInfo,$eventPreregPHP,$eventRegInfo,$eventRegPHP,$vkGroupId)=mysqli_fetch_array($sql);
}

else
{
   $new=1;
   
   list($eventName,$eventStart,$eventStartTime,$eventFinish,$eventFinishTime,$eventPreregStart,$eventRegStart,$eventPreregInfo,$eventPreregPHP,$eventRegInfo,$eventRegPHP,$vkGroupId)=array('',date('Y-m-d'),date('H:00:00'),date('Y-m-d'),date('H:00:00'),date('Y-m-d'),date('Y-m-d'),'','','','',0);
   
}

$groupsList= "<option value=\"0\">Общий для всех групп";

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkApi`");
   
while (list($gId)=mysqli_fetch_array($sql))
{
   $params['group_id'] = $gId;
   //$result=_vkApi_call('groups.getById', $params);
   $groupsList.= "<option value=\"{$gId}\">{$gId}";
   
   $groupName[$vkGroupId]=$gId;
}

$groupsList= str_replace("value=\"{$vkGroupId}\"","value=\"{$vkGroupId}\" selected",$groupsList);

?>

<form action="" method="post">
<?php

if(!$new)
   echo '<input type="hidden" name="eventId" value="'.$eventId.'" /><input type="hidden" name="new" value="0" />';
else
   echo '<input type="hidden" name="eventId" value="" /><input type="hidden" name="new" value="1" />';

?>
<p>vkGroupId: <select name="vkGroupId"><?php echo $groupsList; ?></select></p>
<p>Название: <input type="text" name="eventName" value="<?php if($eventName) echo $eventName; ?>" /></p>
<p>Все даты нужно вводить в формате "гггг-мм-дд"</p>
<p>Дата начала мероприятия: <input type="text" name="eventStart" value="<?php if($eventStart) echo $eventStart; ?>" /> время: <input type="text" name="eventStartTime" value="<?php if($eventStartTime) echo $eventStartTime; ?>" /></p>
<p>Дата окончания мероприятия: <input type="text" name="eventFinish" value="<?php if($eventFinish) echo $eventFinish; ?>" /> время: <input type="text" name="eventFinishTime" value="<?php if($eventFinishTime) echo $eventFinishTime; ?>" /></p>
<p>Дата начала предварительной регистрации: <input type="text" name="eventPreregStart" value="<?php if($eventPreregStart) echo $eventPreregStart; ?>" /></p>
<p>Дата начала регистрации: <input type="text" name="eventRegStart" value="<?php if($eventRegStart) echo $eventRegStart; ?>" /></p>
<p>Описание на время предварительной регистрации:  <textarea name="eventPreregInfo"><?php if($eventPreregInfo) echo $eventPreregInfo; ?></textarea> PHP:  <textarea name="eventPreregPHP"><?php if($eventPreregPHP) echo $eventPreregPHP; ?></textarea></p>

<p>Описание на время регистрации:  <textarea name="eventRegInfo"><?php if($eventRegInfo) echo $eventRegInfo; ?></textarea> PHP:  <textarea name="eventRegPHP"><?php if($eventRegPHP) echo $eventRegPHP; ?></textarea></p>



<?

$fn=0;

echo "

<h2>Список ролей (листов подписки) на мероприятии</h2>

<div id=\"parentId\">";

if(!$new)
{

   $sql=mysqli_query($dblink,"SELECT roleId,roleName,roleMessage,rolePHP FROM `".DBP."events_roles` WHERE eventId={$eventId}");
      
   while (list($roleId,$roleName,$roleMessage,$rolePHP)=mysqli_fetch_array($sql))
   {

         echo "<nobr> <p> id: <input type=\"text\" name=\"roleId[{$fn}]\" value=\"{$roleId}\" /> roleName: <input type=\"text\" name=\"roleName[{$fn}]\" value=\"{$roleName}\" /><br/> Сообщение после регистрации: <textarea name=\"roleMessage[{$fn}]\" style=\"height: 70px; width: 200px\">{$roleMessage}</textarea> PHP после регистрации: <textarea name=\"rolePHP[{$fn}]\" style=\"height: 70px; width: 200px\">{$rolePHP}</textarea><a style=\"color:red;\" onclick=\"return deleteField(this)\" href=\"#\">[—]</a> <a style=\"color:green;\" onclick=\"return addField()\" href=\"#\">[+]</a></p></nobr>";
      
      $fn++;
   }


}

echo "<nobr> <p> id: <input type=\"text\" name=\"roleId[{$fn}]\" value=\"\" /> roleName: <input type=\"text\" name=\"roleName[{$fn}]\" value=\"\" /><br/> Сообщение после регистрации: <textarea name=\"roleMessage[{$fn}]\" style=\"height: 70px; width: 200px\"></textarea> PHP после регистрации: <textarea name=\"rolePHP[{$fn}]\" style=\"height: 70px; width: 200px\"></textarea><a style=\"color:red;\" onclick=\"return deleteField(this)\" href=\"#\">[—]</a> <a style=\"color:green;\" onclick=\"return addField()\" href=\"#\">[+]</a></p></nobr>";

    echo "
</div>";

$fn++;

if(!$new)
{

   $sql=mysqli_query($dblink,"SELECT COUNT(vkId) FROM `".DBP."events_reg_db` WHERE eventId={$eventId}");

   list($nReg)=mysqli_fetch_array($sql);

}
else
   $nReg=0;

if ($nReg)
   echo '<p><br/>
   Что делать с изменениями ролей: <select name="roleChange">
   <option value="0">Игнорировать изменения внесенные в роли
   <option value="1">Я понимаю, что в базе уже есть регистрации и могу все поломать. Хочу сохранить изменения.
   </select></p>';
else
   echo ' <input type="hidden" name="roleChange" value="1" />';

?>

<input type="submit" value="Сохранить" /><br></p>
</form>

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
 div.innerHTML = "<nobr> <p> id: <input type=\"text\" name=\"roleId[" + curFieldNameId + "]\" value=\"\" /> roleName: <input type=\"text\" name=\"roleName[" + curFieldNameId + "]\" value=\"\" /><br/> Сообщение после регистрации: <textarea name=\"roleMessage[" + curFieldNameId + "]\" style=\"height: 70px; width: 200px\"></textarea> PHP после регистрации: <textarea name=\"rolePHP[" + curFieldNameId + "]\" style=\"height: 70px; width: 200px\"></textarea><a style=\"color:red;\" onclick=\"return deleteField(this)\" href=\"#\">[—]</a> <a style=\"color:green;\" onclick=\"return addField()\" href=\"#\">[+]</a></p></nobr>";
 // Добавляем новый узел в конец списка полей
 document.getElementById("parentId").appendChild(div);
 // Возвращаем false, чтобы не было перехода по сслыке
 return false;
}
</script>

<?



require_once(dirname(__FILE__).'/template/bottom.php');

?>
