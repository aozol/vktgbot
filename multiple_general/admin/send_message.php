<?php

require_once(dirname(__FILE__).'/login.php');
//require_once(dirname(__FILE__).'/../adm/functions.php');

$page_title='Отправка сообщения подписчикам';

require_once(dirname(__FILE__).'/template/top.php');

echo '<h1>Отправка сообщения подписчикам</h1>';

$sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']}) OR  (vkGroupId=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']}) GROUP BY mlistId))");


$mlistsArr=array();

$token=$service_token;
//*
while (list($mlistId,$name)=mysqli_fetch_array($sql))
                    $mlistName[$mlistId]=$name;

$mlistsStr=implode ( ',', $mlistsArr);


if (!isset($_POST['listId'])){
   
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
    
    if(in_array (-1, $dataArray['listId']))
        $lists='<option value="-1" selected>Только я (текущий админ)';
    else
        $lists='<option value="-1">Только я (текущий админ)';
    
    if(in_array (-1, $dataArray['listIdExclude']))
        $listsExclude='<option value="-1" selected>Только я (текущий админ)';
    else
        $listsExclude='<option value="-1">Только я (текущий админ)';
    
    
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

    
    
    $sql=mysqli_query($dblink,"SELECT GROUP_CONCAT(vkGroupId) FROM `".DBP."vkAdmin` WHERE vkId={$_COOKIE['vkId']}");

    list($vkGroupIds)=mysqli_fetch_array($sql);
    
    
    
    if($vkGroupIds)
    {
        

        
        $groupsList='<div class="col w6"><div class="content">
        <div class="box header">
        <div class="head"><div></div></div>
        <h2>Приоритетная группа для отправки</h2>
        <div class="desc"><select name="vkGroupId">';
        $params['group_ids'] = $vkGroupIds;
        $result=_vkApi_call('groups.getById', $params);

        foreach($result as $k=> $v)
        {
            if($vkGroupId==$dataArray['vkGroupId'])
                $groupsList.= "<option value=\"{$v['id']}\" selected>{$v['name']}";
            else
                $groupsList.= "<option value=\"{$v['id']}\">{$v['id']}";
             
        }

        $groupsList.='</select></div>
        <div class="bottom"><div></div></div>
        </div>
        </div></div>';
    }
    
    else
        $groupsList='';

   echo '<form id="message" action="" method="post" target="_list">
   
<div class="col w4"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Текст сообщения</h2>
<div class="desc">
<textarea name="text" style="width: 100%; height: 500px">'.$dataArray['text'].'</textarea>
</div>
<div class="bottom"><div></div></div>
</div> 
</div></div>


<div class="col w6"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Шаблоны в сообщении</h2>
<div class="desc">
<p>%name% - имя человека (как указано в вк)</p>
<p>%last_name% - фамилия человека (как указано в вк)</p>
<p>%full_name% - полное имя человека (строится на основе имени из вк: Маша -> Мария)</p>
<p>%t_hi% - Доброе утро/Добрый день/Добрый вечер/Доброй ночи - с большой буквы в зависимости от текущего времени суток</p>
<p>%t_hi_small% - доброе утро/добрый день/добрый вечер/доброй ночи - с маленькой буквы, в зависимости от текущего времени суток</p>
<p>{женское|мужское} - различные варианты в зависимости от пола. Поледовательность именно такая!</p>
</div>
<div class="bottom"><div></div></div>
</div> 
</div></div>

<div class="col w3"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Отправить спискам получателей</h2>
<div class="desc">
<select name="listId[]" multiple>'.$lists.'</select>
</div>
<div class="bottom"><div></div></div>
</div>
</div></div>
<div class="col w3"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Исключить получателей, которые в списках</h2>
<div class="desc">
<select name="listIdExclude[]" multiple>'.$listsExclude.'</select>
</div>
<div class="bottom"><div></div></div>
</div> 
</div></div>
<!--
<div class="col w6"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Вложения</h2>
<div class="desc">
Ссылки на вложения вк через запятую (например, https://vk.com/photo000000_00000000):
<input type="text" name="attachments_str" value="'.$attachments_str.'"/>
</div>
<div class="bottom"><div></div></div>
</div>
</div></div>
-->
'.$groupsList.'
<div class="col w6 last"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Отправка</h2>
<div class="desc">
Дата и время в формате гггг-мм-дд чч:мм:сс (например, 2020-01-25 09:45:00)* <input type="text" name="dateTime" value="'.$dateTime.'"> <input type="submit" value="Запланировать отправку"/><br/>
<em>* Для отправки в ближайшее время укажите дату в прошлом</em>
</div>
<div class="bottom"><div></div></div>
</div> 
</div></div>
';

}



else{


    if (array_search(-1,$_POST['listId'])!==FALSE)
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
        mysqli_query($dblink,"UPDATE `".DBP_GENERAL."message_tasks` SET dateTime='{$_POST['dateTime']}',ADM_VK_ID='".ADM_VK_ID."',DBP='".DBP."',dataJson='".mysqli_escape_string($dblink,json_encode($_POST))."' WHERE taskId={$_GET['taskId']}");
    else
        mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."message_tasks` (dateTime,ADM_VK_ID,DBP,dataJson) VALUES ('{$_POST['dateTime']}','{$_COOKIE['vkId']}','".DBP."','".mysqli_escape_string($dblink,json_encode($_POST))."')");
    
    echo "Задание на отправку добавлено и будет выполнено {$_POST['dateTime']}";
    
}
//*/
require_once(dirname(__FILE__).'/template/bottom.php');


?>
