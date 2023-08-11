<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Редактирование шаблона промокода';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['pcTemplateName']))  //Обработчик сохранения формы
{
   

   
   if ($_POST['new'])
   {
      mysqli_query($dblink,"INSERT INTO `".DBP."promocodeTemplates` (pcTemplateName,pcTemplatePHP) VALUES ('{$_POST['pcTemplateName']}','".mysqli_escape_string($dblink,$_POST['pcTemplatePHP'])."')");
      $_GET['pcTemplateId']=mysqli_insert_id($dblink);
      $pcTemplateId=$_GET['pcTemplateId'];
   }
   else
      mysqli_query($dblink,"UPDATE `".DBP."promocodeTemplates` SET pcTemplatePHP='".mysqli_escape_string($dblink,$_POST['pcTemplatePHP'])."', pcTemplateName='{$_POST['pcTemplateName']}' WHERE  pcTemplateId={$_GET['pcTemplateId']}");
}

if (isset ($_GET['pcTemplateId']))
{
   $pcTemplateId=$_GET['pcTemplateId'];
   $new=0;
   
   $sql=mysqli_query($dblink,"SELECT pcTemplateName,pcTemplatePHP FROM `".DBP."promocodeTemplates` WHERE pcTemplateId='{$pcTemplateId}'");
   
   list($pcTemplateName,$pcTemplatePHP)=mysqli_fetch_array($sql);
}

else
{
   $new=1;
   
   list($pcTemplateName,$pcTemplatePHP)=array('','');
   
}

?>

<form action="" method="post">
<?php

if(!$new)
   echo '<p>Шаблон '.$pcTemplateName.'</p>
   <input type="hidden" name="new" value="0" />
   <input type="hidden" name="pcTemplateId" value="'.$pcTemplateId.'" />';
else
{
   echo '<input type="hidden" name="new" value="1" />';
}
?>
<p>Название шаблона (англ.): <input type="text" name="pcTemplateName" value="<?php if($pcTemplateName) echo $pcTemplateName; ?>" /></p>

<p>Код PHP, выполняющийся при генерации промо-кода:<br><textarea name="pcTemplatePHP" /><?php
if($pcTemplatePHP)
   echo $pcTemplatePHP;
   
else
{
   echo '$promoCode=""; //промокод
$finDate="'.date('Y-m-d').'"; //дата окончания действия
$activationsN=0; //максимальное количество активаций. 0 - безлимитно
$replyText=""; //текст, который нужно отправить пользователю. Если не задан, то будет отправлен только промокод
$actionPHP="<?php ?>" //код действий, которые совершает промо-код';
}
?></textarea><br>
Переменные, которые можно использовать: $currentGroupId - ID группы, в которой генерируется промо-код<br>
$vkId - ID текущего пользователя</p>
<input type="submit" value="Сохранить" /><br><br></p>
</form>

<?




require_once(dirname(__FILE__).'template/bottom.php');

?>
