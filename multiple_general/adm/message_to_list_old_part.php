

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
  
  $sendedN=0;
  $error_str='';
  
  //echo implode(',',$id_result_arr);
  
  foreach ($id_result_arr as $vkId)
  {
    
    $token=$gtoken[$vkGroupId[$vkId]];
    
    //echo $token;
    
    if(isset($_POST['sendNow']))
        if($_POST['sendNow'])
        {
            $pre_message="Результат отправки: отправлено";
            
            $res=vkApi_messagesSendButtons($vkId, $personalText[$vkId],array_merge($buttons,$buttons_def),$attachments);
    
            if (is_array($res))
            {
                if (isset($res['error_code']))
                {
                    if ($res['error_code']==901)
                    {
                        user_mlist_manage($vkId,$_POST['listId'],0,$vkGroupId[$vkId]);
                        $error_str.= "{$vkId}: unsub done: user_mlist_manage({$vkId},{$_POST['listId']},0,{$vkGroupId[$vkId]})<br>";
                    }
                    else
                        $error_str.= $vkId.': error '.$res['error_code'].'<br>';
                }
                else
                    $error_str.= $vkId.': unknown error<br>';
                
            }
            
            else
                $sendedN++;//echo $vkId.': '.$res.'<br>';
        }
        
        else
        {
            $pre_message="Результат отправки: на {$_POST['dateTime']} запланирована отправка";
            
            eval($_POST['buttons_php']);
            
            if($buttons[0][0][0]["payload"])
            {
                $conf_str = file_get_contents ('../conf.php');

                $buttons_def_str=preg_replace('#.*//кнопки по умолчанию(.*)//конец кнопок по умолчанию.*#s','$1',$conf_str);
                $_POST['buttons_php'].=$buttons_def_str;
            }
            $sendResult=message_queue($token,$vkId,$personalText[$vkId],$_POST['dateTime'],$_POST['buttons_php']);
            
            if($sendResult[0])
                $sendedN++;//echo "{$vkId}: отправка запланирована на {$_POST['dateTime']}<br>";
            else
            {
                $error_str.= "{$vkId}: отправка отклонена, т.к. ранее была ошибка {$sendResult[1]}";
                if($sendResult[1]==901)
                {
                    user_mlist_manage($vkId,$_POST['listId'],0,$vkGroupId[$vkId]);
                    $error_str.= "- <strong>пользователь отписан от рассылки</strong>";
                }
                $error_str.= "<br>";
                
                mysqli_query($dblink,"DELETE FROM `".DBP_GENERAL."message_error`  WHERE token='{$token}' AND vkId={$vkId}");
            }
        }
    else
        {
            $pre_message="Результат отправки: на {$_POST['dateTime']} запланирована отправка";
            
            eval($_POST['buttons_php']);
            
            if($buttons[0][0][0]["payload"])
            {
                $conf_str = file_get_contents ('../conf.php');

                $buttons_def_str=preg_replace('#.*//кнопки по умолчанию(.*)//конец кнопок по умолчанию.*#s','$1',$conf_str);
                $_POST['buttons_php'].=$buttons_def_str;
                
            }
            $sendResult=message_queue($token,$vkId,$personalText[$vkId],$_POST['dateTime'],$_POST['buttons_php']);
            
            if($sendResult[0])
                $sendedN++;//echo "{$vkId}: отправка запланирована на {$_POST['dateTime']}<br>";
            else
            {
                $error_str.= "{$vkId}: отправка отклонена, т.к. ранее была ошибка {$sendResult[1]}";
                if($sendResult[1]==901)
                {
                    user_mlist_manage($vkId,$_POST['listId'],0,$vkGroupId[$vkId]);
                    $error_str.= "- <strong>пользователь отписан от рассылки</strong>";
                }
                $error_str.= "<br>";
                
                mysqli_query($dblink,"DELETE FROM `".DBP_GENERAL."message_error`  WHERE token='{$token}' AND vkId={$vkId}");
            }
            
        }
    
    
     
  }
  
  
  echo "<p>{$pre_message} {$sendedN} сообщений.</p>";
    
    if($error_str)
        echo "<p><strong>Ошибки при отправке:</strong></p><p>{$error_str}</p>";
  
