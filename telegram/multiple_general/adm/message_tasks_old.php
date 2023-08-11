<?php

require_once(dirname(__FILE__).'/functions.php');

$nowDate=date('Y-m-d H:i:00');

$sql=mysqli_query($dblink,"SELECT ADM_VK_ID,DBP,dataJson FROM `".DBP_GENERAL."message_tasks` WHERE dateTime<='{$nowDate}' AND isDone=0");


  
while (list($ADM_VK_ID,$DBP,$dataJson)=mysqli_fetch_array($sql))
{


    $result_message='';
    
    $dataArray=json_decode($dataJson, TRUE);

  $id_arr=array();
  $id_arr_exclude=array();

  $text=$dataArray['text'];
  
  if ($dataArray['buttons_php'])
     eval($dataArray['buttons_php']);
  
  if ($dataArray['vkIds'])
     $id_str=get_vk_ids($dataArray['vkIds'],'string');
  else $id_str='0';


  if ($dataArray['vkIdsExclude'])
     $id_str_exclude=get_vk_ids($dataArray['vkIdsExclude'],'string');
  else $id_str_exclude='0';

  

  $lists_str=implode(',',$dataArray['listId']);
  
  $lists_exclude_str=implode(',',$dataArray['listIdExclude']);
  
  if ($dataArray['vkGroupId']) //если указана приоритетная группа, то сначала сортируем, чтобы она была первой
     $sql_str="SELECT vkId,vkGroupId FROM (SELECT vkId,vkGroupId FROM `".$DBP."db` WHERE (unsub=0 AND mlistId IN ({$lists_str})) OR vkId IN ({$id_str}) ORDER BY ABS(`vkGroupId`-{$dataArray['vkGroupId']}) ASC) AS sortedTbl GROUP BY vkId";
  else $sql_str="SELECT vkId,vkGroupId,userSex FROM `".$DBP."db` WHERE ( unsub=0 AND mlistId IN ({$lists_str}) ) OR vkId IN ({$id_str}) GROUP BY vkId";
  
  //$result_message.= $sql_str;
  
  $sql=mysqli_query($dblink,$sql_str);
  
  $vkGroupId=array();
  while (list($vkId,$gId,$sex)=mysqli_fetch_array($sql))
  {
    $id_arr[]=$vkId;
    $vkGroupId[$vkId]=$gId;
    $vkSex[$vkId]=$gId;
  }
  
  $sql=mysqli_query($dblink,"SELECT vkId FROM `".$DBP."db` WHERE (unsub=0 AND mlistId IN ({$lists_exclude_str})) OR vkId IN ({$id_str_exclude})");
  
  while (list($vkId)=mysqli_fetch_array($sql))
    $id_arr_exclude[]=$vkId;
    
  $id_arr_exclude[]=0;
  $id_result_arr=array_diff ($id_arr, $id_arr_exclude);
  $id_result_arr= array_unique ($id_result_arr, SORT_NUMERIC);
  
  $sql=mysqli_query($dblink,"SELECT token,vkGroupId FROM `".$DBP."vkApi`");
  
  $gtoken=array();
  while(list($t,$gId)=mysqli_fetch_array($sql))
     $gtoken[$gId]=$t;
     
    $token=$service_token;


  $personalText=personal_text(implode(',',$id_result_arr),$text);
  
  $sendedN=0;
  $error_str='';
  
  //$result_message.= implode(',',$id_result_arr);
  

            
            if($buttons[0][0][0]["payload"])
            {
                $conf_str = $dataArray['conf_str'];

                $buttons_def_str=preg_replace('#.*//кнопки по умолчанию(.*)//конец кнопок по умолчанию.*#s','$1',$conf_str);
                $dataArray['buttons_php'].=$buttons_def_str;
            }
  
  foreach ($id_result_arr as $vkId)
  {
    
    $token=$gtoken[$vkGroupId[$vkId]];
    
    //$result_message.= $token;
    
    if(isset($dataArray['sendNow']))
        if($dataArray['sendNow'])
        {
            $pre_message="Результат отправки: отправлено";
            
            $res=vkApi_messagesSendButtons($vkId, $personalText[$vkId],array_merge($buttons,$buttons_def),$attachments);
    
            if (is_array($res))
            {
                if (isset($res['error_code']))
                {
                    if ($res['error_code']==901)
                    {
                        user_mlist_manage($vkId,$dataArray['listId'],0,$vkGroupId[$vkId]);
                        $error_str.= "{$vkId}: unsub done: user_mlist_manage({$vkId},{$dataArray['listId']},0,{$vkGroupId[$vkId]})<br>";
                    }
                    else
                        $error_str.= $vkId.': error '.$res['error_code'].'<br>';
                }
                else
                    $error_str.= $vkId.': unknown error<br>';
                
            }
            
            else
                $sendedN++;//$result_message.= $vkId.': '.$res.'<br>';
        }
        
        else
        {
            $pre_message="Результат отправки: на {$dataArray['dateTime']} запланирована отправка";
            
            
            $sendResult=message_queue($token,$vkId,$personalText[$vkId],date('Y-m-d H:i:00',time()-3600),$dataArray['buttons_php']);
            
            if($sendResult[0])
                $sendedN++;//$result_message.= "{$vkId}: отправка запланирована на {$dataArray['dateTime']}<br>";
            else
            {
                $error_str.= "{$vkId}: отправка отклонена, т.к. ранее была ошибка {$sendResult[1]}";
                if($sendResult[1]==901)
                {
                    user_mlist_manage($vkId,$dataArray['listId'],0,$vkGroupId[$vkId]);
                    $error_str.= "- <strong>пользователь отписан от рассылки</strong>";
                }
                $error_str.= "<br>";
                
                mysqli_query($dblink,"DELETE FROM `".DBP_GENERAL."message_error`  WHERE token='{$token}' AND vkId={$vkId}");
            }
        }
    else
        {
            $pre_message="Результат отправки: на {$dataArray['dateTime']} запланирована отправка";
            
            
            $sendResult=message_queue($token,$vkId,$personalText[$vkId],date('Y-m-d H:i:00',time()-3600),$dataArray['buttons_php']);
            
            if($sendResult[0])
                $sendedN++;//$result_message.= "{$vkId}: отправка запланирована на {$dataArray['dateTime']}<br>";
            else
            {
                $error_str.= "{$vkId}: отправка отклонена, т.к. ранее была ошибка {$sendResult[1]}";
                if($sendResult[1]==901)
                {
                    user_mlist_manage($vkId,$dataArray['listId'],0,$vkGroupId[$vkId]);
                    $error_str.= "- <strong>пользователь отписан от рассылки</strong>";
                }
                $error_str.= "<br>";
                
                mysqli_query($dblink,"DELETE FROM `".DBP_GENERAL."message_error`  WHERE token='{$token}' AND vkId={$vkId}");
            }
            
        }
    
    
     
  }
  
  
  $result_message.= "<p>{$pre_message} {$sendedN} сообщений.</p>";
    
    if($error_str)
        $result_message.= "<p><strong>Ошибки при отправке:</strong></p><p>{$error_str}</p>";
  

    if($result_message)
    {
        bot_debugger("Результат выполнения задания по рассылке: <br>".$result_message);
        echo $result_message;
    }
}

mysqli_query($dblink,"UPDATE `".DBP_GENERAL."message_tasks` SET isDone=1  WHERE dateTime<='{$nowDate}'");

echo file_get_contents('http://vkbot.clubevrika.ru/multiple_general/adm/message_sender.php?from_tasks=1');



?>
