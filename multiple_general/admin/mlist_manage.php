<?php

require_once(dirname(__FILE__).'/../functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование списка рассылки';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['mlistId']))  //Обработчик сохранения формы
{
   
   $_GET['mlistId']=$_POST['mlistId'];
   $mlistId=$_GET['mlistId'];
   
   if ($_POST['new'])
      mysqli_query($dblink,"INSERT INTO `".DBP."mlists` (mlistId,mlistName,isPublic,vkGroupId) VALUES ({$mlistId},'".mysqli_escape_string($dblink,$_POST['mlistName'])."',{$_POST['isPublic']},{$_POST['vkGroupId']})");
   else
      mysqli_query($dblink,"UPDATE `".DBP."mlists` SET mlistName='".mysqli_escape_string($dblink,$_POST['mlistName'])."', isPublic={$_POST['isPublic']}, vkGroupId={$_POST['vkGroupId']} WHERE mlistId={$mlistId}");
   
   if($_POST['isDefault'])
   {
      mysqli_query($dblink,"UPDATE `".DBP."mlists` SET isDefault=1 WHERE mlistId={$mlistId}");
      mysqli_query($dblink,"UPDATE `".DBP."mlists` SET isDefault=0 WHERE mlistId!={$mlistId} AND vkGroupId={$_POST['vkGroupId']}");
   }
   
   else
      mysqli_query($dblink,"UPDATE `".DBP."mlists` SET isDefault=0 WHERE mlistId={$mlistId}");
}

if (isset ($_GET['mlistId']))
{
   $mlistId=$_GET['mlistId'];
   $new=0;
   
   $sql=mysqli_query($dblink,"SELECT mlistName,isPublic,isDefault,vkGroupId FROM `".DBP."mlists` WHERE mlistId={$mlistId}");
   
   list($mlistName,$isPublic,$isDefault,$vkGroupId)=mysqli_fetch_array($sql);
}

else
{
   $new=1;
   
   $sql=mysqli_query($dblink,"SELECT MAX(mlistId) FROM `".DBP."mlists`");
   
   list($mlistId)=mysqli_fetch_array($sql);
   $mlistId++;
   $_GET['mlistId']=$mlistId;
   
   list($mlistName,$isPublic,$isDefault,$vkGroupId)=array('',0,0,0);
   
}

$groupsList= "<option value=\"0\">Общий для всех групп";

$sql=mysqli_query($dblink,"SELECT vkGroupId FROM `".DBP."vkApi`");
   
while (list($gId)=mysqli_fetch_array($sql))
{
   $params['group_id'] = $gId;
   $result=_vkApi_call('groups.getById', $params);
   $groupsList.= "<option value=\"{$gId}\">{$result[0]['name']}";
   
   $groupName[$vkGroupId]=$result[0]['name'];
}

$groupsList= str_replace("value=\"{$vkGroupId}\"","value=\"{$vkGroupId}\" selected",$groupsList);

?>

<form action="" method="post">
<?php

if(!$new)
   echo '<input type="hidden" name="mlistId" value="'.$mlistId.'" /><input type="hidden" name="new" value="0" />';
else
{
   echo '<input type="hidden" name="new" value="1" /><input type="hidden" name="mlistId" value="'.$mlistId.'" />';
   /*<p>mlistId: <input type="text" name="mlistId" value="" /> Занятые id: ';
   $sql=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists`");
      
   while (list($mlId)=mysqli_fetch_array($sql))
      echo $mlId.' ';
   
   echo '</p>';
   */
}
?>
<table>
<tr><td>Название скиска</td><td><input type="text" name="mlistName" value="<?php if($mlistName) echo $mlistName; ?>" /></td></tr>
<tr><td>Группа</td><td><select name="vkGroupId"><?php echo $groupsList; ?></select></td></tr>
<tr><td>Тип списка</td><td><select name="isPublic"><option value="1"<?php if($isPublic) echo ' selected'; ?>>Открытый для подписки
<option value="0"<?php if(!$isPublic) echo ' selected'; ?>>Закрытый
</select></td></tr>
<tr><td>Список по умолчанию* для группы </td><td><select name="isDefault"><option value="1"<?php if($isDefault) echo ' selected'; ?>>Да
<option value="0"<?php if(!$isDefault) echo ' selected'; ?>>Нет
</select></td></tr>
<tr><td colspan="2"><input type="submit" value="Сохранить" /></td></tr>
</table>
<p>* на который подписывать пользователя при первом сообщении</p>
</form>

<?




require_once(dirname(__FILE__).'template/bottom.php');

?>
