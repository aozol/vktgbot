<?php



require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Отправка сообщения подписчикам';

require_once(dirname(__FILE__).'/template/top.php');




if (!isset($_POST['listId'])){


   $text='';

    if ($_GET['taskId'])
    {
        $sql=mysqli_query($dblink,"SELECT dateTime,dataJson FROM `".DBP_GENERAL."message_tasks` WHERE taskId={$_GET['taskId']} AND DBP='".DBP."'");
        if (list($dateTime,$dataJson)=mysqli_fetch_array($sql))
            $dataArray=json_decode($dataJson, TRUE);
        else
            $dataArray=array();
    }
    else
        $dataArray=array();


    if ($adm_info['root'])
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi`");
    else
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}");
        
    $groupsList.= "<option value=\"0\">Нет";
   
   while (list($vkGroupId)=mysqli_fetch_array($sql))
   {
      $params['group_id'] = $vkGroupId;
      $result=_vkApi_call('groups.getById', $params);
      if($vkGroupId==$dataArray['vkGroupId'])
        $groupsList.= "<option value=\"{$vkGroupId}\" selected>{$result[0]['name']}";
    else
        $groupsList.= "<option value=\"{$vkGroupId}\">{$result[0]['name']}";
      
      $groupName[$vkGroupId]=$result[0]['name'];
   }

        
    if ($adm_info['root'])
        $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
    else
        $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']})");
        


    while (list($mlistId,$name)=mysqli_fetch_array($sql))
                    $mlistName[$mlistId]=$name;
    
    $lists='';
    $listsExclude='';
    
    foreach ($mlistName as $k=>$v)
    {
    
        if(in_array ($k, $dataArray['listId']))
            $lists.='<option value="'.$k.'" selected>'.$v;
        else
            $lists.='<option value="'.$k.'">'.$v;
        
        if(in_array ($k, $dataArray['listIdExclude']))
            $listsExclude.='<option value="'.$k.'" selected>'.$v;
        else
            $listsExclude.='<option value="'.$k.'">'.$v;        
    
    
    }
    
    if (!$dataArray['dateTime'])
        $dataArray['dateTime']=date("Y-m-d H:i:00");


   echo '<form action="" method="post" target="_list">
<p>Отправить сообщение списку получателей:<br/><br/>
Отправить (cписок): <select name="listId[]" multiple>'.$lists.'</select><br/>
Дополнительные Vkid через запятую: <input type="text" name="vkIds" value=""></p>
<p>Исключить получателей, которые также подписаны на списки:<br/><br/>
Исключить (cписок): <select name="listIdExclude[]" multiple>'.$listsExclude.'</select><br/>
Дополнительные исключаемые Vkid через запятую: <input type="text" name="vkIdsExclude" value=""></p>
<p>Приориетная группа: <select name="vkGroupId">'.$groupsList.'</select></p>
<p>Дата отправки: <input type="text" name="dateTime" value="'.$dataArray['dateTime'].'"></p>
<p>Сообщение:</p>

<p><textarea name="text" style="width: 300px; height: 200px">'.$dataArray['text'].'</textarea>
<br/>
%name% - имя человека (как указано в вк)<br/>
%last_name% - фамилия человека (как указано в вк)<br/>
%full_name% - полное имя человека (строится на основе имени из вк: Маша -> Мария)<br/>
%t_hi% - Доброе утро/Добрый день/Добрый вечер/Доброй ночи - в зависимости от текущего времени суток<br/>
%t_hi_small% - доброе утро/добрый день/добрый вечер/доброй ночи - с маленькой буквы, в зависимости от текущего времени суток<br/>
{женское|мужское} - различные варианты в зависимости от пола. Поледовательность именно такая!</p>
<p>Кнопки бота:<br/>
<textarea name="buttons_php" style="width: 300px; height: 200px">'.$dataArray['buttons_php'].'</textarea></p>
<p>Шаблон кнопки:<br/><br/>
$buttons[0][0][0]["payload"]="command";<br/>
$buttons[0][0][1]="Описание на кнопке";<br/>
$buttons[0][0][2]="default";<br/><br/>
$attachments - массив вложений для прикрепления к сообщению</p>

<p><input type="submit" value="Отправить"></p>
</form><br><br>';

}



else{
    
    $_POST['conf_str'] = file_get_contents ('../conf.php');
    
    if(isset($_GET['taskId']) AND !isset($_GET['copy']))
        mysqli_query($dblink,"UPDATE `".DBP_GENERAL."message_tasks` SET dateTime='{$_POST['dateTime']}',ADM_VK_ID='".ADM_VK_ID."',DBP='".DBP."',dataJson='".mysqli_escape_string($dblink,json_encode($_POST))."' WHERE taskId={$_GET['taskId']}");
    else
        mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."message_tasks` (dateTime,ADM_VK_ID,DBP,dataJson) VALUES ('{$_POST['dateTime']}','".ADM_VK_ID."','".DBP."','".mysqli_escape_string($dblink,json_encode($_POST))."')");
    
    echo "Задание на отправку добавлено и будет выполнено {$_POST['dateTime']}";
    



}

require_once(dirname(__FILE__).'template/bottom.php');


?>
