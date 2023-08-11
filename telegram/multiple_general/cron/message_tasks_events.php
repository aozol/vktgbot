<?php

/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/

require_once(dirname(__FILE__).'/../adm/functions.php');
echo 'cron "Message tasks"<br>';

$nowDate=date('Y-m-d H:i:00');

$sql=mysqli_query($dblink,"SELECT taskId,ADM_VK_ID,DBP,dataJson FROM `".DBP_GENERAL."message_tasks` WHERE dateTime<='{$nowDate}' AND isDone=0 LIMIT 0,5");

echo 'n:'.mysqli_num_rows($sql).'<br>';
$i=0;

while (list($taskId,$ADM_VK_ID,$DBP,$dataJson)=mysqli_fetch_array($sql))
{
    $i++;
    
    echo "Number {$i}";
    
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

  //type - по умолчанию будет рассылка по списку, но также может быть рассылка по событию (мероприятию)
  if(!isset($dataArray['type']))
    $dataArray['type']='mlist';
  
  switch($dataArray['type'])
  {
    case 'mlists':
    default:

      $lists_str=implode(',',$dataArray['listId']);
      
      $lists_exclude_str=implode(',',$dataArray['listIdExclude']);
      
      if ($dataArray['vkGroupId']) //если указана приоритетная группа, то сначала сортируем, чтобы она была первой
        $sql_str="SELECT vkId,vkGroupId FROM (SELECT DISTINCT(vkId),vkGroupId FROM `".$DBP."db` WHERE (unsub=0 AND mlistId IN ({$lists_str})) OR vkId IN ({$id_str}) ORDER BY ABS(CAST(`vkGroupId` AS SIGNED)-{$dataArray['vkGroupId']}) ASC) AS sortedTbl GROUP BY vkId";
      else $sql_str="SELECT vkId,vkGroupId FROM `".$DBP."db` WHERE ( unsub=0 AND mlistId IN ({$lists_str}) ) OR vkId IN ({$id_str}) GROUP BY vkId";
      
      //$result_message.= $sql_str;
      
      echo $sql_str;
      
      $sql2=mysqli_query($dblink,$sql_str);
      
      $vkGroupId=array();
      while (list($vkId,$gId)=mysqli_fetch_array($sql2))
      {
        $id_arr[]=$vkId;
        $vkGroupId[$vkId]=$gId;

      }
      
      $sql2=mysqli_query($dblink,"SELECT vkId FROM `".$DBP."db` WHERE (unsub=0 AND mlistId IN ({$lists_exclude_str})) OR vkId IN ({$id_str_exclude})");
      
      while (list($vkId)=mysqli_fetch_array($sql2))
        $id_arr_exclude[]=$vkId;
    break;
  }
    
  $id_arr_exclude[]=0;
  $id_result_arr=array_diff ($id_arr, $id_arr_exclude);
  $id_result_arr= array_unique ($id_result_arr, SORT_NUMERIC);
  
  $sql2=mysqli_query($dblink,"SELECT token,vkGroupId FROM `".$DBP."vkApi`");
  
  $gtoken=array();
  while(list($t,$gId)=mysqli_fetch_array($sql2))
     $gtoken[$gId]=$t;
     
    $token=$service_token;
    


  $personalText=personal_text(implode(',',$id_result_arr),$text);
  
  $sendedN=0;
  $error_str='';
  
  //$result_message.= implode(',',$id_result_arr);
  

            
            if(1) //в телеграм всегда отправляем кнопки по умолчанию ($buttons[0][0][0]["payload"])
            {
                $conf_str = $dataArray['conf_str'];

                $buttons_def_str=preg_replace('#.*//кнопки по умолчанию(.*)//конец кнопок по умолчанию.*#s','$1',$conf_str);
                $dataArray['buttons_php'].=$buttons_def_str;
            }
  
  foreach ($id_result_arr as $vkId)
  {
    
    $token=$gtoken[$vkGroupId[$vkId]];
    

            $pre_message="Результат отправки: на {$dataArray['dateTime']} запланирована отправка";
            
            //echo $vkId.'<br>';
            
            $sendResult=message_queue($token,$vkId,$personalText[$vkId],$dataArray['dateTime'],$dataArray['buttons_php']);
            
            if($sendResult[0])
                $sendedN++;//$result_message.= "{$vkId}: отправка запланирована на {$dataArray['dateTime']}<br>";
            else
            {
                $error_str.= "https://vk.com/id{$vkId}: отправка отклонена, т.к. ранее была ошибка {$sendResult[1]}";
                if($sendResult[1]==403)
                {
                    user_mlist_manage($vkId,$dataArray['listId'],0,$vkGroupId[$vkId]);
                    $error_str.= "- пользователь отписан от рассылки<br>";
                }
                $error_str.= "<br>";
                
                mysqli_query($dblink,"DELETE FROM `".DBP_GENERAL."message_error`  WHERE token='{$token}' AND vkId={$vkId}");
            }
            

    
    
     
  }
  
  
  $result_message.= "{$pre_message} {$sendedN} сообщений.<br>";
    
    if($error_str)
        $result_message.= "Ошибки при отправке:<br>{$error_str}";
  

    if($result_message)
    {
        bot_debugger("Результат выполнения задания номер {$taskId} по рассылке: <br>".$result_message);
        echo $result_message;
    }
    
    mysqli_query($dblink,"UPDATE `".DBP_GENERAL."message_tasks` SET isDone=1  WHERE taskId={$taskId}");
    
    
    unset($dataArray);
    unset($vkGroupId);
    unset($gtoken);
    unset($personalText);
}



//echo file_get_contents('http://vkbot.clubevrika.ru/multiple_general/adm/message_sender.php?from_tasks=1');



?>
