<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Отправка сообщения подписчикам';

require_once(dirname(__FILE__).'/template/top.php');

if ($adm_info['root'])
    $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
else
    $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists` WHERE vkGroupId IN (SELECT vkGroupId FROM `".DBP."vkApi` WHERE admin={$adm_info['id']})");
    


             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;

foreach ($mlistName as $k=>$v)
{
  
  $lists.='<option value="'.$k.'">'.$v;
  
}


if (!isset($_POST['listId'])){


   $text='';

if ($adm_info['root'])
    $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi`");
else
    $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}");
    
   $groupsList.= "<option value=\"0\">Нет";
   
   while (list($vkGroupId)=mysqli_fetch_array($sql))
   {
      $params['group_id'] = $vkGroupId;
      $result=_vkApi_call('groups.getById', $params);
      $groupsList.= "<option value=\"{$vkGroupId}\">{$result[0]['name']}";
      
      $groupName[$vkGroupId]=$result[0]['name'];
   }


   echo '<form action="" method="post" target="_list">
<p>Отправить сообщение списку получателей:<br/><br/>
Отправить (cписок): <select name="listId[]" multiple>'.$lists.'</select><br/>
Дополнительные Vkid через запятую: <input type="text" name="vkIds" value=""></p>
<p>Исключить получателей, которые также подписаны на списки:<br/><br/>
Исключить (cписок): <select name="listIdExclude[]" multiple>'.$lists.'</select><br/>
Дополнительные исключаемые Vkid через запятую: <input type="text" name="vkIdsExclude" value=""></p>
<p>Приориетная группа: <select name="vkGroupId">'.$groupsList.'</select></p>
<p>Сообщение:</p>

<p><textarea name="text" style="width: 300px; height: 200px">'.$text.'</textarea>
<br/>
%name% - имя человека (как указано в вк)<br/>
%last_name% - фамилия человека (как указано в вк)<br/>
%full_name% - полное имя человека (строится на основе имени из вк: Маша -> Мария)<br/>
%t_hi% - Доброе утро/Добрый день/Добрый вечер/Доброй ночи - в зависимости от текущего времени суток<br/>
%t_hi_small% - доброе утро/добрый день/добрый вечер/доброй ночи - с маленькой буквы, в зависимости от текущего времени суток<br/>
{женское|мужское} - различные варианты в зависимости от пола. Поледовательность именно такая!</p>
<p>Кнопки бота:<br/>
<textarea name="buttons_php" style="width: 300px; height: 200px">/* Чтобы добавить кнопки к кнопкам по умолчанию, введите их здесь в нужном порядке */</textarea></p>
<p>Шаблон кнопки:<br/><br/>
$buttons[0][0][0]["payload"]="command";<br/>
$buttons[0][0][1]="Описание на кнопке";<br/>
$buttons[0][0][2]="default";</p>

<p><input type="submit" value="Отправить"></p>
</form><br><br>';

}



else{



  $id_arr=array();
  $id_arr_exclude=array();


  $text=$_POST['text'];
  
  if ($_POST['buttons_php'])
     eval($_POST['buttons_php']);
  
  if ($_POST['vkIds'])
     $id_str=get_vk_ids($_POST['vkIds'],'string');
  else $id_str='0';

  if ($_POST['vkIdsExclude'])
     $id_str_exclude=get_vk_ids($_POST['vkIdsExclude'],'string');
  else $id_str_exclude='0';
  

  $lists_str=implode(',',$_POST['listId']);
  
  $lists_exclude_str=implode(',',$_POST['listIdExclude']);
  
  if ($_POST['vkGroupId']) //если указана приоритетная группа, то сначала сортируем, чтобы она была первой
     $sql_str="SELECT vkId,vkGroupId FROM (SELECT vkId,vkGroupId FROM `".DBP."db` WHERE (unsub=0 AND mlistId IN ({$lists_str})) OR vkId IN ({$id_str}) ORDER BY ABS(`vkGroupId`-{$_POST['vkGroupId']}) ASC) AS sortedTbl GROUP BY vkId";
  else $sql_str="SELECT vkId,vkGroupId,userSex FROM `".DBP."db` WHERE ( unsub=0 AND mlistId IN ({$lists_str}) ) OR vkId IN ({$id_str}) GROUP BY vkId";
  
  //echo $sql_str;
  
  $sql=mysqli_query($dblink,$sql_str);
  
  $vkGroupId=array();
  while (list($vkId,$gId,$sex)=mysqli_fetch_array($sql))
  {
    $id_arr[]=$vkId;
    $vkGroupId[$vkId]=$gId;
    $vkSex[$vkId]=$gId;
  }
  $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."db` WHERE (unsub=0 AND mlistId IN ({$lists_exclude_str})) OR vkId IN ({$id_str_exclude})");
  
   
  
  while (list($vkId)=mysqli_fetch_array($sql))
    $id_arr_exclude[]=$vkId;
    
  $id_arr_exclude[]=0;
  $id_result_arr=array_diff ($id_arr, $id_arr_exclude);
  
  $sql=mysqli_query($dblink,"SELECT token,vkGroupId FROM `".DBP."vkApi`");
  
  $gtoken=array();
  while(list($t,$gId)=mysqli_fetch_array($sql))
     $gtoken[$gId]=$t;  

  $personalText=personal_text(implode(',',$id_result_arr),$text);
  
  foreach ($id_result_arr as $vkId)
  {
    
    $token=$gtoken[$vkGroupId[$vkId]];
    
    $res=vkApi_messagesSendButtons($vkId, $personalText[$vkId],array_merge($buttons,$buttons_def),$attachments);
    
    if (is_array($res))
    {
      if (isset($res['error_code']))
      {
        if ($res['error_code']==901)
          {
            user_mlist_manage($vkId,$_POST['listId'],0,$vkGroupId[$vkId]);
            echo "{$vkId}: unsub done: user_mlist_manage({$vkId},{$_POST['listId']},0,{$vkGroupId[$vkId]})<br>";
          }
        else
          echo $vkId.': error '.$res['error_code'].'<br>';
      }
      else
        echo $vkId.': unknown error<br>';
      
    }
    
    else
      echo $vkId.': '.$res.'<br>';
  }
  

}

require_once(dirname(__FILE__).'template/bottom.php');


?>
