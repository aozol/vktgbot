<?php

require_once(dirname(__FILE__).'/login.php');
//require_once(dirname(__FILE__).'/../adm/functions.php');

$page_title='Отправка сообщения подписчикам';

require_once(dirname(__FILE__).'/template/top.php');

echo '<h1>Отправка сообщения подписчикам Шуткобота</h1>';

$sql=mysqli_query($dblink,"SELECT mlistId,vkGroupId FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."shutkobotAdmin` WHERE vkId={$_COOKIE['vkId']})");

$lists='<option value="-1">Только я (текущий админ)';
$mlistsArr=array();

$token=$service_token;
//*
while (list($mlistId,$vkGroupId)=mysqli_fetch_array($sql))
{
    $params['group_id'] = $vkGroupId;
    $result=_vkApi_call('groups.getById', $params);
    $mlistsArr[]=$mlistId;
    $lists.='<option value="'.$mlistId.'">'.$result[0]['name'];
}

$mlistsStr=implode ( ',', $mlistsArr);


if (!isset($_POST['listId'])){


   $text='';



   echo '<form id="message" action="" method="post" target="_list">
   
<div class="col w4"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Текст сообщения</h2>
<div class="desc">
<textarea name="text" style="width: 100%; height: 500px">'.$text.'</textarea>
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
<h2>Списки получателей</h2>
<div class="desc">
<select name="listId[]" multiple>'.$lists.'</select>
</div>
<div class="bottom"><div></div></div>
</div>
</div></div>
<div class="col w3"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Исключить получателей, которые в списке</h2>
<div class="desc">
<select name="listIdExclude[]" multiple>'.$lists.'</select>
</div>
<div class="bottom"><div></div></div>
</div> 
</div></div>




<div class="col w6 last"><div class="content">
<div class="box header">
<div class="head"><div></div></div>
<h2>Отправка</h2>
<div class="desc">
Дата и время* <input type="text" name="dateTime" value="'.date("Y-m-d H:i:00").'"> <input type="submit" value="Запланировать отправку"/><br/>
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
    
    $_POST['conf_str'] = file_get_contents ('../conf.php');
    
    mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."message_tasks` (dateTime,ADM_VK_ID,DBP,dataJson) VALUES ('{$_POST['dateTime']}','{$_COOKIE['vkId']}','".DBP."','".mysqli_escape_string($dblink,json_encode($_POST))."')");
    
    echo "Задание на отправку добавлено и будет выполнено {$_POST['dateTime']}";
    
}
//*/
require_once(dirname(__FILE__).'template/bottom.php');


?>
