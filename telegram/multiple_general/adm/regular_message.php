<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');


$page_title='Планирование регулярного сообщения';

require_once(dirname(__FILE__).'/template/top.php');

echo '<h1>Планирование регулярного сообщения</h1>';

    


if (!isset($_POST['listId'])){
   
   if ($_GET['taskId'])
    {
        $sql=mysqli_query($dblink,"SELECT month,day,weekday,hour,minute,dataJson FROM `".DBP_GENERAL."message_regular_tasks` WHERE taskId={$_GET['taskId']} AND DBP='".DBP."'");
        if (list($month,$day,$weekday,$hour,$minute,$dataJson)=mysqli_fetch_array($sql))
            $dataArray=json_decode($dataJson, TRUE);
        else
        {
            $dataArray=array();
            list($month,$day,$weekday,$hour,$minute)=array(0,0,0,0,0);
        }
    }
    else
    {
        $dataArray=array();
        list($month,$day,$weekday,$hour,$minute)=array(0,0,0,0,0);
    }

    if ($adm_info['root'])
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi`");
    else
        $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}");
        
    $groupsList= "<option value=\"0\">Нет";
   
    while (list($vkGroupId)=mysqli_fetch_array($sql))
    {
        //$params['group_id'] = $vkGroupId;
        //$result=_vkApi_call('groups.getById', $params);
        $result[0]['name']=$vkGroupId;
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


    $mlistsArr=array();

    $token=$service_token;
    //*
    while (list($mlistId,$name)=mysqli_fetch_array($sql))
                        $mlistName[$mlistId]=$name;

    $mlistsStr=implode ( ',', $mlistsArr);
    
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
        
    if (!$dataArray['php'])
        $attachments_str='';
    else
    {
        eval ($dataArray['php']);
        $attachments_str=implode(',',$attachments);
    }


   echo '<form action="" method="post" target="_list">
<p>Отправить сообщение списку получателей:<br/><br/>
Отправить (cписок): <select name="listId[]" multiple>'.$lists.'</select><br/>
Дополнительные Vkid через запятую: <input type="text" name="vkIds" value=""></p>
<p>Исключить получателей, которые также подписаны на списки:<br/><br/>
Исключить (cписок): <select name="listIdExclude[]" multiple>'.$listsExclude.'</select><br/>
Дополнительные исключаемые Vkid через запятую: <input type="text" name="vkIdsExclude" value=""></p>
<p>Приориетная группа: <select name="vkGroupId">'.$groupsList.'</select></p>
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
<h2>Отправка</h2>
<p>Отправлять сообщение:<br>
Месяц* <input type="text" name="month" value="'.$month.'" class="two_digits"><br/>
День месяца* <input type="text" name="day" value="'.$day.'" class="two_digits"><br/>
День недели** <input type="text" name="weekday" value="'.$weekday.'" class="two_digits"><br/>
Час <input type="text" name="hour" value="'.$hour.'" class="two_digits"><br/>
Минуты *** <input type="text" name="minute" value="'.$minute.'" class="two_digits"><br/>

<em>* Чтобы отправлять без ограничений (каждый месяц/день), укажите 0<br/>
** От 1 - понедельник, до 7 - воскресенье. 0 - каждый день<br/>
*** Только значения, кратные 10</em></p>

<p><input type="submit" value="Запланировать отправку"></p>
</form><br><br>';

}



else{


    if (array_search(-123456,$_POST['listId'])!==FALSE)
        $_POST['vkIds']=$_COOKIE['vkId'];
        
        
    if (isset($_POST['listIdExclude'][0]) AND (array_search(-1,$_POST['listIdExclude'])!==FALSE))
        $_POST['vkIdsExclude']=$_COOKIE['vkId'];
        
    if ($_POST['attachments_str'])
    {
        $attachments_str=str_replace('m.vk','vk',$_POST['attachments_str']);
        $attachments_str=str_replace('https://vk.com/','',$attachments_str);
        $_POST['php']='$attachments=array(\''.str_replace(",","','",$attachments_str).'\');';
    }    
    
    $_POST['conf_str'] = file_get_contents ('../conf.php');
    
    
    if(isset($_GET['taskId']) AND !isset($_GET['copy']))
        mysqli_query($dblink,"UPDATE `".DBP_GENERAL."message_regular_tasks` SET month='{$_POST['month']}',day='{$_POST['day']}',weekday='{$_POST['weekday']}',hour='{$_POST['hour']}',minute='{$_POST['minute']}',ADM_VK_ID='".ADM_VK_ID."',DBP='".DBP."',dataJson='".mysqli_escape_string($dblink,json_encode($_POST))."' WHERE taskId={$_GET['taskId']}");
    else
        mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."message_regular_tasks` (month,day,weekday,hour,minute,ADM_VK_ID,DBP,dataJson) VALUES ({$_POST['month']},{$_POST['day']},{$_POST['weekday']},{$_POST['hour']},{$_POST['minute']},'{$_COOKIE['vkId']}','".DBP."','".mysqli_escape_string($dblink,json_encode($_POST))."')");
    
    echo "Задание на регулярную отправку добавлено";
    
}
//*/
require_once(dirname(__FILE__).'template/bottom.php');


?>
