<?php
require_once('conf.php');



if (!isset($_REQUEST)) {
    return;
}

require_once(dirname(__FILE__).'/functions.php');
require_once(GENERAL_DIR.'/functions.php');


//Получаем и декодируем уведомление

if (isset($_GET['emulate']))
{
   if(gethostbyname($_SERVER['SERVER_NAME'])!=$_SERVER['REMOTE_ADDR'])
   	exit;
   
   $data = json_decode($_POST[0]);
}
else
   $data = json_decode(file_get_contents('php://input'));



$currentGroupId=$data->group_id;

$sql=mysqli_query($dblink,"SELECT token,confirmToken,secret FROM `".DBP."vkApi` WHERE vkGroupId={$currentGroupId}");
list($token,$confirmationToken,$secretKey)=mysqli_fetch_array($sql);

// проверяем secretKey
if(strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0)
    return;

//Проверяем, что находится в поле "type"
switch ($data->type) {
    //Если это уведомление для подтверждения адреса с$userIdервера...
    case 'confirmation':
        //...отправляем строку для подтверждения адреса
        
        
        
        
        echo $confirmationToken;
        break;

    //Если это уведомление о новом сообщении...
    case 'message_new':
        //...получаем id его автора и текст сообщения
        
        
          
        
        $vkId = $data->object->message->peer_id;
                
        $incommingText = trim($data->object->message->text);
        
        if(isset($data->object->message->payload))
        {
          $pl=json_decode($data->object->message->payload);
          $payloadText = $pl->payload;
        }
          
        else
          $payloadText = '';
          

        if (($incommingText=='Начать') OR ($incommingText=='Start') OR ($incommingText=='начать') OR ($incommingText=='start') )
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'shutkobot_start';
        }
        
        if (($incommingText=='shutkobot_test_by_admin'))
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'shutkobot_test_by_admin';
        }
        

        
        if (user_auth($vkId)) //Нового пользователя - подписываем на рассылку и отправляем первое сообщение что бы он там ни написал
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'shutkobot_start';
        }
        
        if(!$payloadText) //Проверяем, нет ли у пользователя текущего состояния, которое задает payloadText; Также проверяем, не является ли текст командой из меню
        {
           $sql=mysqli_query($dblink,"SELECT payloadText FROM `".DBP."userState` WHERE vkId={$vkId} AND vkGroupId IN (0,{$currentGroupId})");
           while (list($pT)=mysqli_fetch_array($sql))
              $payloadText=$pT;
            
            $sql=mysqli_query($dblink,"SELECT payloadText FROM `".DBP."keyboard` WHERE vkId={$vkId} AND buttonText='{$incommingText}'");
           while (list($pT)=mysqli_fetch_array($sql))
              $payloadText=$pT;
        }
        
        
        

        if (stristr($payloadText,':'))
        {
          $pt=stristr($payloadText,':',1);
          $payloadParams=str_replace($pt.':','',$payloadText);
          $payloadText=$pt;
        }
        
        
        if (($incommingText=='меню') OR ($incommingText=='Меню'))
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'shutkobot_menu';
        }        
        
        switch ($payloadText)
        {
          
           case "shutkobot_menu":
            
             $sql=mysqli_query($dblink,"SELECT buttonText FROM `".DBP."keyboard` WHERE vkId={$vkId}");
             
             if(!mysqli_num_rows($sql))
                bot_debugger("Ошибка вызова меню для {$vkId}");
            else
            {
                $text="Для использования команды вместо кнопки просто скопируйте и отправьте текст команды в ответ. Вам на данный момент доступны следующие команды:";
                
                vkApi_messagesSend($vkId, $text);
                
                while (list($buttonText)=mysqli_fetch_array($sql))
                    vkApi_messagesSend($vkId, $buttonText);
            }
             
             
             user_mlist_manage($vkId,$payloadParams,1);
             
             $text='Вы успешно подписаны на рассылку: "'.$mlistName[$payloadParams].'"!';
             
             
          
          break;
           
           
           case "shutkobot_subscription":
             
             if (isset($payloadParams))
                $n_start=$payloadParams;
            else
                $n_start=0;
             
             $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;
             
             $sql=mysqli_query($dblink,"SELECT mlistId,unsub FROM `".DBP."db` WHERE vkId={$vkId} GROUP BY mlistId");
             
             $lists_str='-1';
             $sub_str='';
             $unsub_str='';
             $sub_buttons=array();
             $unsub_buttons=array();
             $s=0;
             $us=0;
             
             
             while (list($mlistId,$unsub)=mysqli_fetch_array($sql))
             {
                $lists_str.=','.$mlistId;
                
                if ($unsub)
                {
                   $unsub_str.='
- '.$mlistName[$mlistId];
                   
                   $sub_buttons[$s/2][$s%2][0]["payload"]="sub:".$mlistId;
                   $sub_buttons[$s/2][$s%2][1]='Подписаться: '.$mlistName[$mlistId];
                   $sub_buttons[$s/2][$s%2][2]='positive';
                   
                   $s++;
                }
                
                else
                {
                   $sub_str.='
- '.$mlistName[$mlistId];
                   
                   $unsub_buttons[$us/2][$us%2][0]["payload"]="shutkobot_unsub:".$mlistId;
                   $unsub_buttons[$us/2][$us%2][1]='Отписаться: '.$mlistName[$mlistId];
                   $unsub_buttons[$us/2][$us%2][2]='negative';
                   
                   $us++;
                }
             }
             
             $sql=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists` WHERE isPublic=1 AND mlistId NOT IN ({$lists_str})");
                          
             while (list($mlistId)=mysqli_fetch_array($sql))
             {
               $unsub_str.='
- '.$mlistName[$mlistId];
                   
               $sub_buttons[$s/2][$s%2][0]["payload"]="shutkobot_sub:".$mlistId;
               $sub_buttons[$s/2][$s%2][1]='Подписаться: '.$mlistName[$mlistId];
               $sub_buttons[$s/2][$s%2][2]='positive';
                   
               $s++;
             }
             
             if(!$sub_str)
                $sub_str='
                (пусто)';
             
             if(!$unsub_str)
                $unsub_str='
                (пусто)';
                
             $text='Здравствуйте, %name%!
На данный момент вы подписаны на следующие рассылки:'.$sub_str.'

Список рассылок, на которые вы можете подписаться дополнительно:'.$unsub_str.'

Для управления подпиской используйте кнопки ниже.';
             
             $txt=personal_text($vkId, $text);
             vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($sub_buttons,$unsub_buttons,$buttons,$buttons_def));
           
          break;
          
          case "shutkobot_sub":
            
             $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;
             
             user_mlist_manage($vkId,$payloadParams,1);
             
             $text='Вы успешно подписаны на рассылку: "'.$mlistName[$payloadParams].'"!';
             
             vkApi_messagesSendButtons($vkId, $text,array_merge($buttons,$buttons_def));
          
          break;
          
          case "shutkobot_unsub":
          
             $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;
             
             user_mlist_manage($vkId,$payloadParams,0);
             
             $text='Вы успешно отписаны от рассылки: "'.$mlistName[$payloadParams].'"!';
             
             vkApi_messagesSendButtons($vkId, $text,array_merge($buttons,$buttons_def));
             
             //vkApi_messagesSend(ADM_VK_ID, "user_mlist_manage($vkId,$payloadParams,0);");
          
          break;
          

          
          
          case 'shutkobot_start':
             $replyText='Привет, %name%!

Тут можно пошутить, посмеяться над шутками других и посмотреть, как твою шутку оценивают.

Раз в несколько дней бот присылает начало для шутки (зачин), твоя задача придумать и отправить смешное окончание (добивку). Нажми "Добить", чтобы попробовать себя в юморе!

После того, как шутки собраны, можно за них голосовать. Нажми "Голосовать" - и появятся шутки и кнопки для оценки. Совсем несмешно - это 1, а если шутка чертовски смешная - 13.

Итоги голосования подводятся в момент появления нового зачина - и можно увидеть 3 лучшие шутки предыдущего дня по кнопке "Смотреть лучшие".

А по кнопке "Мои шутки" можно смотреть твои шутки, видеть их рейтинг и управлять ими.

Дерзай! Нажми "Добить" прямо сейчас!';


            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
          break;
          
          case 'shutkobot_return':
            
            
                
            $replyText="";
            

            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],$buttons_def,$attachments);
          break;
          
          case 'shutkobot_my':
            
            $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE isActive=1 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");
            


            list($nowStartId)=mysqli_fetch_array($sql);
            
                
            $replyText="Здесь вы можете управлять своими шутками";
            
            $buttons_my[1][0][0]["payload"]="shutkobot_my_best:{$nowStartId}";
            $buttons_my[1][0][1]="Топ предыдущего голосования";
            $buttons_my[1][0][2]="positive"; //primary, positive, negative
            
            
            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons_my,$buttons_def),$attachments);
          break;
          
          case 'shutkobot_my_edit':
          
            $userState='shutkobot_my_edit';
            
            if($incommingText==$buttons_my[2][0][1])
            {
                
                $sql=mysqli_query($dblink,"SELECT startId,startText FROM `".DBP."starts` WHERE isActive=1 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");
                
                list($startIdActive,$startText)=mysqli_fetch_array($sql);
                
                $sql=mysqli_query($dblink,"SELECT finishId,finishText FROM `".DBP."finishes` WHERE vkId={$vkId} AND startId={$startIdActive}");
                
                $replyText="Для того, чтобы изменить свою добивку, отправь сообщение с текстом \"[номер добивки]::[новый текст]\" (два двоеточия обязательны, кавычки не нужны).<br><br>Твои добивки на зачин \"{$startText}\" (с их номерами):";
                
                while(list($finishId,$finishText)=mysqli_fetch_array($sql))
                {
                    $replyText.="<br><br>{$finishId}. {$finishText}";
                }
            }
            
            else
            {
                $finishId=stristr($incommingText,'::',1);
                $finishText=str_replace($finishId.'::','',$incommingText);
                
                mysqli_query($dblink,"UPDATE `".DBP."finishes` SET finishText='{$finishText}' WHERE finishId={$finishId}");
                
                $replyText="Добивка {$finishId} изменена на '{$finishText}'. Можешь изменить еще одну, отправив соответствующую команду.";
                
            }
            
            

            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons_my,$buttons_def),$attachments);
          break;
          
          case 'shutkobot_my_best':
            
            $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE isActive=1 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");
            
            list($nowStartId)=mysqli_fetch_array($sql);
            
            //bot_debugger("SELECT finishId,startId,avgVote,medVote,finishText FROM `".DBP."finishes` WHERE startId={$payloadParams}");
            
            if(!$payloadParams)
            {
                $sql=mysqli_query($dblink,"SELECT finishId,startId,avgVote,medVote,finishText FROM `".DBP."finishes` WHERE vkId={$vkId} AND avgVote!=0 AND startId IN (SELECT startId FROM `".DBP."starts` WHERE isActive=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})) ORDER BY avgVote DESC LIMIT 0,5");
                $replyText='Ваши лучшие шутки за все время (по средней оценке):<br><br>';
             }
             elseif($payloadParams=='medVote')
             {
                $sql=mysqli_query($dblink,"SELECT finishId,startId,avgVote,medVote,finishText FROM `".DBP."finishes` WHERE vkId={$vkId} AND avgVote!=0 AND startId IN (SELECT startId FROM `".DBP."starts` WHERE isActive=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})) ORDER BY medVote DESC LIMIT 0,5");
                $replyText='Ваши лучшие шутки за все время (по медианной оценке):<br><br>';
             }
             else
             {
                
                $nowStartId=$payloadParams;
                $sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE startId={$nowStartId}");
                list($startDate)=mysqli_fetch_array($sql);
                
                $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE startDate<'{$startDate}' AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId}) AND startId IN (SELECT startId FROM `".DBP."finishes` WHERE vkId={$vkId} GROUP BY startId) ORDER BY startDate DESC");
                list($nowStartId)=mysqli_fetch_array($sql);
                
                //bot_debugger("SELECT startId FROM `".DBP."starts` WHERE startDate<'{$startDate}' AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId}) ORDER BY startDate DESC");
                
                $sql=mysqli_query($dblink,"SELECT finishId,startId,avgVote,medVote,finishText FROM `".DBP."finishes` WHERE vkId={$vkId} AND startId={$nowStartId} ORDER BY avgVote DESC LIMIT 0,5");
             }
             
            $buttons_my[1][0][0]["payload"]="shutkobot_my_best:{$nowStartId}";
            $buttons_my[1][0][1]="Топ предыдущего голосования";
            $buttons_my[1][0][2]="positive"; //primary, positive, negative
            
            if(!mysqli_num_rows($sql))
                $replyText.="Пока что в базе нет твоих шуток ((<br><br>Нажми \"Добить\" и отправь свою первую!";

   
            while(list($finishId,$startId,$avgVote,$medVote,$finishText)=mysqli_fetch_array($sql))
            {
                
                if(!$avgVote)
                {
                    list($avgVote,$medVote)=get_avg_med($finishId);
                }
                
                $sql_start=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");
                list($startText)=mysqli_fetch_array($sql_start);
                
                $replyText.="\"{$startText} {$finishText}\". Средняя оценка: {$avgVote}, медианная оценка: {$medVote}<br><br>";
            }


            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons_my,$buttons_def),$attachments);
          break;
          
                    
          case 'shutkobot_best':
             $sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE isActive=1 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");

   
            list($startDate)=mysqli_fetch_array($sql);

            $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE startDate<='{$startDate}' AND isActive=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId}) ORDER BY startDate DESC");
            
            list($startId)=mysqli_fetch_array($sql);

            $sql=mysqli_query($dblink,"SELECT count(finishId) FROM `".DBP."votes` WHERE startId={$startId} AND vkId={$vkId}");

            list($n_voted)=mysqli_fetch_array($sql);

            $sql=mysqli_query($dblink,"SELECT count(finishId) FROM `".DBP."finishes` WHERE startId={$startId}");
            list($n_finishes)=mysqli_fetch_array($sql);

            $n_to_vote=min(3,$n_finishes);

            if($n_voted<$n_to_vote)
            $replyText='Чтобы увидеть лучшие, оцени хотя бы '.$n_to_vote.' шутки из предыдущего раунда! (по кнопке "Голосовать")';

            else
            {

            if (isset($payloadParams))
            {
		    $sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE startId={$payloadParams}");

		    list($startDate)=mysqli_fetch_array($sql);

		    $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE startDate<='{$startDate}' AND isActive=0 AND startId!={$payloadParams} AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId}) ORDER BY startDate DESC");
		    
		    list($startId)=mysqli_fetch_array($sql);

		    //vkApi_messagesSend(ADM_VK_ID, "PP");

            }

            else
            {
		    $sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE isActive=1 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");
		    
		    list($startDate)=mysqli_fetch_array($sql);

		    $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE startDate<='{$startDate}' AND isActive=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId}) ORDER BY startDate DESC");
		    
		    list($startId)=mysqli_fetch_array($sql);
		    list($startId)=mysqli_fetch_array($sql);
            }

            $sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");
            
            list($startText)=mysqli_fetch_array($sql);

            $replyText='Лучшие шутки прошлого голосования:';



            $place=array();

            $sql=mysqli_query($dblink,"SELECT finishId,finishText FROM `".DBP."finishes` WHERE startId={$startId} ORDER BY avgVote DESC");

            $n=1;

            while (($n<=10) AND list($finishId,$fT)=mysqli_fetch_array($sql))
            {
                

                $finishText[$finishId]=$fT;
                $place[$finishId]=$n-0.05;
                $n++;
            }

            $sql=mysqli_query($dblink,"SELECT finishId,finishText FROM `".DBP."finishes` WHERE startId={$startId} ORDER BY medVote DESC, avgVote DESC");

            $n=1;

            while (($n<=10) AND list($finishId,$fT)=mysqli_fetch_array($sql))
            {
                
                $finishText[$finishId]=$fT;
                
                if(isset($place[$finishId]))
                    $place[$finishId]+=$n;
                else
                    $place[$finishId]=$n+10;
                $n++;
            }

            asort($place);
            
            //bot_debugger(print_r($place,TRUE));

            $n=1;
            foreach ($place as $finishId=>$p)
            {
            if ($n>3) break;

            $replyText.='

'.$startText." {$finishText[$finishId]}";
            $n++;
            }

            $buttons[0][0][0]["payload"]="shutkobot_best:{$startId}";
            $buttons[0][0][1]="Смотреть еще лучшие";
            $buttons[0][0][2]="positive"; //primary, positive, negative


            }


            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
          break;
          
          case 'shutkobot_give_finish':
            $userState='shutkobot_give_finish';

            $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE isActive=1 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");
            
            list($startId)=mysqli_fetch_array($sql);

            $sql=mysqli_query($dblink,"SELECT finishLimit FROM `".DBP."rating` WHERE vkId={$vkId}");
            
            if(!mysqli_num_rows($sql))
            {
                mysqli_query($dblink,"INSERT INTO `".DBP."rating` (vkId) VALUES ({$vkId})");
                $sql=mysqli_query($dblink,"SELECT finishLimit FROM `".DBP."rating` WHERE vkId={$vkId}");
            }
            
            list($finishLimit)=mysqli_fetch_array($sql);
            
            $sql=mysqli_query($dblink,"SELECT count(vkId) FROM `".DBP."finishes` WHERE vkId={$vkId} AND startId={$startId}");
            
            //vkApi_messagesSend(ADM_VK_ID, "INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'{$incommingText}',{$vkId})");

            list($nFinishes)=mysqli_fetch_array($sql);

            //vkApi_messagesSend(ADM_VK_ID, "SELECT count(vkId) FROM `".DBP."finishes` WHERE vkId={$vkId} AND startId={$startId}");


            if ($incommingText!='Добить')
            {

            
            if($nFinishes<$finishLimit)
            {
                mysqli_query($dblink,"INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'{$incommingText}',{$vkId})");

                //vkApi_messagesSend(ADM_VK_ID, "INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'{$incommingText}',{$vkId})");
                $nFinishes++;
                
                if(!($finishLimit-$nFinishes))
                    $replyText='Добивка записана, на сегодня твои добивки закончились';
                else
                    $replyText='Добивка записана, можешь добавить еще '.($finishLimit-$nFinishes);
                
                

            }
            
            else
            {
                $replyText='Сегодня твой лимит на добивки закончился:( Добивка не сохранена';
            }

            }

            else
            {
            
                $sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");
                
                list($startText)=mysqli_fetch_array($sql);
                
                $replyText="Добей шутку: {$startText}


P.S.: Сегодня ты можешь предложить еще ".($finishLimit-$nFinishes)." добивок";
            }
            
            $buttons[0]=$buttons_my[2];


            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
          break;
          
          case 'shutkobot_give_start':
            $userState='shutkobot_give_start';

            if ($incommingText!='Предложить зачин')
            {
                $sql=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId} AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");
                
                list($mlistId)=mysqli_fetch_array($sql);

                mysqli_query($dblink,"INSERT INTO `".DBP."starts_new` (mlistId,startText) VALUES ({$mlistId},'{$incommingText}')");

                //bot_debugger("INSERT INTO `".DBP."starts_new` (mlistId,startText) VALUES ({$mlistId},'{$incommingText}')");


                $replyText='Зачин записан, можешь добавить еще один.
                
ВНИМАНИЕ!!!
Мы сохранили твое предложение для НАЧАЛА ШУТКИ (зачин), к которому другие будут придумывать финал (если админы его одобрят).
Если на самом деле ты хочешь предложить смешное завершение (добивку), то для этого надо НАЖАТЬ КНОПКУ "ДОБИТЬ"!';
            }
            
            else
                $replyText='Пиши предложения зачина ниже';



            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
          break;
          
          case 'shutkobot_vote':
            $sql=mysqli_query($dblink,"SELECT startDate FROM `".DBP."starts` WHERE isActive=1 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId}) AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId})");

            
            list($startDate)=mysqli_fetch_array($sql);

            $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE startDate<='{$startDate}' AND isActive=0 AND mlistId IN (SELECT mlistId FROM `".DBP."db` WHERE vkId={$vkId})  AND mlistId IN (SELECT mlistId FROM `".DBP."mlists` WHERE vkGroupId={$currentGroupId}) ORDER BY startDate DESC");
            
            list($startId)=mysqli_fetch_array($sql);



            if ($incommingText!='Голосовать')
            {
                
                mysqli_query($dblink,"INSERT INTO `".DBP."votes` (startId,finishId,vkId,vote) VALUES ({$startId},{$payloadParams},{$vkId},{$incommingText})");
                

            }

            mysqli_query($dblink,"SET group_concat_max_len=4294967295");

            // Выбрать список всех ID из таблицы


            $sql=mysqli_query($dblink,"SELECT GROUP_CONCAT(`finishId` SEPARATOR ',') AS `finishId_list` FROM `".DBP."finishes` WHERE startId={$startId} AND finishId NOT IN (SELECT finishId FROM `".DBP."votes` WHERE startId={$startId} AND vkId={$vkId})");

            list($row)=mysqli_fetch_array($sql);

            //*


            // Преобразовать строку в массив ID
            $id_list=explode(',',$row);
            $n=count($id_list);

            if(!$row)
                $replyText='Шутки кончились( Но зато теперь ты можешь посмотреть лучшие за прошлое голосование!
А еще можешь добить текущий зачин, чтобы создать больше шуток!';
                
            else
            {
                $finishId=$id_list[rand(0,$n-1)];
                    
                    
                $sql=mysqli_query($dblink,"SELECT finishText FROM `".DBP."finishes` WHERE startId={$startId} AND finishId={$finishId}");
                

                list($finishText)=mysqli_fetch_array($sql);

                $sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");
                list($startText)=mysqli_fetch_array($sql);
                $replyText='Голосуй за шутку: '.$startText." {$finishText}";

                $buttons[0][0][0]["payload"]="vote:".$finishId;
                $buttons[0][0][1]='1';
                $buttons[0][0][2]='positive';

                $buttons[0][1][0]["payload"]="vote:".$finishId;
                $buttons[0][1][1]='3';
                $buttons[0][1][2]='positive';

                $buttons[0][2][0]["payload"]="vote:".$finishId;
                $buttons[0][2][1]='5';
                $buttons[0][2][2]='positive';

                $buttons[1][0][0]["payload"]="vote:".$finishId;
                $buttons[1][0][1]='8';
                $buttons[1][0][2]='positive';

                $buttons[1][1][0]["payload"]="vote:".$finishId;
                $buttons[1][1][1]='13';
                $buttons[1][1][2]='positive';
            }
            //*/


            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
          break;
          
          

          case 'shutkobot_share':
          	
          	$params['group_id'] = $currentGroupId;
          	$result=_vkApi_call('groups.getById', $params);
          	
          	
          	$text="Приглашаю присоединиться к Шуткоботу \"{$result[0]['name']}\"!

Там можно пошутить самому, а также почитать и оценить шутки других участников.

Переходи по ссылке: https://vk.com/im?sel=-{$currentGroupId}

";
		$token1=$token;
		$token=$service_token;
		$params['owner_id'] = -$currentGroupId;
		$params['album_id'] = 'profile';
		$params['rev'] = 1;
          	$result=_vkApi_call('photos.get', $params);
          	$token=$token1;
          	//$text.=print_r($result,TRUE);
          	//$text.="photo_-{$currentGroupId}_{$result['items'][0]['id']}";
		
		$attachments[]="photo-{$currentGroupId}_{$result['items'][0]['id']}";
		vkApi_messagesSend($vkId, 'Чтобы пригласить друзей в Шуткобота, перешли сообщение ниже. Также можешь разместить его постом на своей странице.');
          	vkApi_messagesSendButtons($vkId, $text,array_merge($buttons,$buttons_def),$attachments);
          break;
          
          case 'shutkobot_create':
          	
          	$text="Чтобы создать отдельного Шуткобота для своей компании/организации, переходи по ссылке и следуй подсказкам установщика: http://vkbot.clubevrika.ru/shutkobot2/install/";
		vkApi_messagesSendButtons($vkId, $text,array_merge($buttons,$buttons_def),$attachments);
          break;
          
          
          
          
          
          
          
          
          default:
          
            if (!$payloadText)
            {
                              
               // Проверка, является ли сообщение промо-кодом
               $sql=mysqli_query($dblink,"SELECT finDate, activationsN,activatedN, actionPHP FROM `".DBP."promocodes` WHERE promoCode='{$incommingText}' ORDER BY finDate DESC");
            
               if(list($finDate, $activationsN,$activatedN, $actionPHP)=mysqli_fetch_array($sql))
               {
                   
                   $promocodeWorks=0;
                   
                   if($finDate<date('Y-m-d'))
                   {
                      $replyText='Cрок действия данного промо-кода истек - попробуйте использовать другой. Чтобы связаться с администратором по любым вопросам, просто ответьте на это сообщение.';
                   }
                   
                   elseif(($activationsN)&($activationsN<=$activatedN))
                   {
                      $replyText='Данный промо-код уже был активирован максимально возможное число раз - попробуйте использовать другой. Чтобы связаться с администратором по любым вопросам, просто ответьте на это сообщение.';
                   }
                   
                   else
                   {
                      $promoCode=$incommingText;
                      eval($actionPHP);
                   }
                   
                   if (isset($replyText))
                      if($replyText)
                      {
                         $txt=personal_text($vkId, $replyText);
                         vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
                      }
                      
                   if ($promocodeWorks)
                      mysqli_query($dblink,"UPDATE `".DBP."promocodes` SET activatedN=".($activatedN+1)." WHERE promoCode='{$incommingText}' AND finDate='{$finDate}'");
               }
            }
            else
            {
            
            $attachments=array();
            $buttons=array();
            $sql=mysqli_query($dblink,"SELECT replyText,php FROM `".DBP."botReply` WHERE vkGroupId IN ({$currentGroupId},-1) AND payloadText='{$payloadText}'");
            
            if(!mysqli_num_rows($sql))
            {
               bot_debugger("Неизвестный payload {$payloadText} для группы {$currentGroupId} отправлен {$vkId} vk.com/id{$vkId}, emulate: {$_GET['emulate']}. MY IP: ".gethostbyname($_SERVER['SERVER_NAME']).", REMOTE IP: {$_SERVER['REMOTE_ADDR']}");
               break;
            }
            
            else
               list($replyText,$php)=mysqli_fetch_array($sql);
            
            $userState='';
            
            eval ($php);
            
            mysqli_query($dblink,"DELETE FROM `".DBP."userState` WHERE vkId={$vkId} AND vkGroupId IN (0,{$currentGroupId})");
            
            if($userState)
               mysqli_query($dblink,"INSERT INTO `".DBP."userState` (vkId,payloadText,vkGroupId) VALUES ({$vkId},'{$userState}',{$currentGroupId})");
            
            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
            }
            
          break;
          
          
          

        }
        
        if (isset($userState)) if ($userState)
        {
               mysqli_query($dblink,"DELETE FROM `".DBP."userState` WHERE vkId={$vkId} AND vkGroupId IN (0,{$currentGroupId})");
               mysqli_query($dblink,"INSERT INTO `".DBP."userState` (vkId,payloadText,vkGroupId) VALUES ({$vkId},'{$userState}',{$currentGroupId})");
         }      
         

        //Возвращаем "ok" серверу Callback API
        echo('ok');

        break;

        
        default:
        echo('ok');

        break;
        
}
?>
