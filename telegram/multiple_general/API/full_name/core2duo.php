<?php


require_once (dirname(__FILE__).'/conf.php');
  if (!mysql_connect($db_server, $db_login, $db_pass)) { echo 'Ошибка подключения к базе'; exit; }
  mysql_query("SET NAMES 'utf8'"); //на всякий случай, бывают проблемы с русскими буквами
  mysql_select_db($db_name);
  
require_once (dirname(__FILE__).'/account_params.php');


function gq($data, $table) { //функция генерации INSERT запроса из массива, part of MFiCMS
  $qd = array();
  while (list($key, $val) = each($data)) {
    $qd[$key]='"'.mysql_real_escape_string($val).'"';
  }
  return "INSERT INTO ".$table." VALUES (".implode(', ',$qd).")";
}

function sendmail($from_name,$from_address,$message,$subject = '',$to_bcc = array()) {

$from_address='=?UTF-8?B?'.base64_encode($from_name).'?= <'.$from_address.'>';
  $headers = 'From: '.$from_address."\n";
  $headers .= 'Reply-To: '.$from_address."\n";
  $headers .= 'Return-Path: '.$from_address."\n";
  $headers .= "MIME-Version: 1.0\nContent-type: text/plain; charset=utf-8\n";
  @mail($to_bcc, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
  return true;  
  
}

function R_strlow ($str){
if (mb_strtolower ("Ж")!="ж") $str = strtr(
$str,
'ЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ',
'йцукенгшщзхъфывапролджэячсмитьбю');
else $str=mb_strtolower($str);
return $str;
};


function rus_date() {
    $translate = array(
    "am" => "дп",
    "pm" => "пп",
    "AM" => "ДП",
    "PM" => "ПП",
    "Monday" => "Понедельник",
    "Mon" => "Пн",
    "Tuesday" => "Вторник",
    "Tue" => "Вт",
    "Wednesday" => "Среда",
    "Wed" => "Ср",
    "Thursday" => "Четверг",
    "Thu" => "Чт",
    "Friday" => "Пятница",
    "Fri" => "Пт",
    "Saturday" => "Суббота",
    "Sat" => "Сб",
    "Sunday" => "Воскресенье",
    "Sun" => "Вс",
    "January" => "Января",
    "Jan" => "Янв",
    "February" => "Февраля",
    "Feb" => "Фев",
    "March" => "Марта",
    "Mar" => "Мар",
    "April" => "Апреля",
    "Apr" => "Апр",
    "May" => "Мая",
    "May" => "Мая",
    "June" => "Июня",
    "Jun" => "Июн",
    "July" => "Июля",
    "Jul" => "Июл",
    "August" => "Августа",
    "Aug" => "Авг",
    "September" => "Сентября",
    "Sep" => "Сен",
    "October" => "Октября",
    "Oct" => "Окт",
    "November" => "Ноября",
    "Nov" => "Ноя",
    "December" => "Декабря",
    "Dec" => "Дек",
    "st" => "ое",
    "nd" => "ое",
    "rd" => "е",
    "th" => "ое"
    );
    
    if (func_num_args() > 1) {
        $timestamp = func_get_arg(1);
        return strtr(date(func_get_arg(0), $timestamp), $translate);
    } else {
        return strtr(date(func_get_arg(0)), $translate);
    }
}







// -----------------------




function message($uid,$account,$text,$title=''){


//получаем token для аккаунта
$sql=mysql_query("SELECT token,active FROM `".DBP."vk_account` WHERE id=$account");
list($token,$active)=mysql_fetch_array($sql);

if (!$active) return 5; //если аккаунт не активен, возвращаем сообщение о блокировке

//получаем имя пользователя
$sRequest = "https://api.vk.com/method/users.get?user_ids=$uid&fields=sex&lang=ru";
$oResponce = json_decode(file_get_contents($sRequest), true);
$name=$oResponce['response'][0]['first_name'];
if (isset ($oResponce['response'][0]['sex'])) $sex=$oResponce['response'][0]['sex']; else $sex=0;
if(isset($oResponce['error']['error_code'])) return $oResponce['error']['error_code'];

$text=str_replace('%name%',full_name($name,$sex),$text);

$text=urlencode($text);
$title=urlencode($title);

$sRequest = "https://api.vk.com/method/messages.send?uid=$uid&message=$text&title=$title&access_token=$token";


// ответ от Вконтакте

$oResponce = json_decode(file_get_contents($sRequest), true);
//echo $sRequest;

if(isset($oResponce['error']['error_code'])) $error=$oResponce['error']['error_code'];
else $error=0;

if($error==5){
mysql_query("UPDATE ".DBP."vk_account SET `active`=0 WHERE `id`=".$account."");

$message='Только что заблокирован аккаунт '.$account.'.
Перейти к разблокировке: http://intservis.ru/_adm_/vk-campaign/unfreeze.php?st=2&account='.$account;
$subject = 'Заблокирован аккаунт '.$account;
global $from_name,$from_mail,$adm_mail;
sendmail($from_name,$from_mail,$message,$subject,$adm_mail);
}

return $error;

}


function API($method, $sett)
{
    global $now_token;
    $ch = curl_init('https://api.vk.com/method/' . $method . '.json?' . http_build_query($sett) . '&access_token=' . $now_token);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}




function dump($id)
{
    $history='<p>';
    $start='';
    $messages = array();
    
    $info      = API('getProfiles', array(
        'uid' => $id,
        'fields' => 'photo'
    ));
	if(empty($info['response'])) { 
		die('<pre>Error</pre>');
	}
	
    $s_name    = $info['response'][0]['first_name']; // 
    $s_surname = $info['response'][0]['last_name']; // -- Граббинг инфы о собеседнике
    $s_photo   = $info['response'][0]['photo']; // //
    $s_tabname = $s_name . " " . $s_surname;

    $name    = 'Я'; // 
 /*   $surname = '('.$myname.')'; // -- Граббинг инфы о себе
    $photo   = 'my_foto.jpg'; // //
*/    
    
    # Let`s get is started!
/*    $page  = API('messages.getHistory', array(
        'uid' => $id,
        'count' => '1'
    ));
    $count = (int) $page['response'][0]; // Количество сообщений с данным человеком
    
    $first      = $count % 100; // API позволяет получать не больше 100 сообщений за раз, сначала получим те, которые не получить при count = 100
    $iterations = ($count - $first) / 100; // Сколько раз получать по 100 сообщений
*/    
    $page = API('messages.getHistory', array(
        'uid' => $id,
        'count' => 100
        ));
    unset($page['response'][0]); // Количество сообшений мы уже знаем
    $messages = array_values($page['response']); // ВК отдает сообщения сверху вниз
    
/*    
    for ($i = 1; $i > 0; $i--) {
        $page = API('messages.getHistory', array(
            'uid' => $id,
            'count' => 100,
            'offset' => (string) ($i * 100)
        ));
        unset($page['response'][0]);
        $messages = array_merge($messages, array_values($page['response']));
    }
 */   
   // $page  = str_replace('%username%', $s_tabname, file_get_contents('head.tpl')); // Замена названия на вкладке
    $lines = array(); // Линии файла упрощенного стиля
    
    $h='';
    
    foreach ($messages as $msg) { // Обрабатываем каждое сообщение
 
        if ($msg['from_id'] != $id) {
            $tname  = "$name";
           
            $tid    = $id;
        } else {
            $tname  = "$s_name";
            
            $tid    = 0;
        }
        
        
        $body = $msg['body'];
        $date = (string) ((int) $msg['date'] + 3600);
        $time = date("d.m.Y H:i", $date);
        
        $lines[] = "$tname ($time): $body";
        $h .= $start.'<strong>'.$tname.' </strong> ('.$time.'):<br/>'.$body;
        $start='<br/><br/>
        ------------<br/><br/>';
    }
    //$page .= file_get_contents('foot.tpl');
    
    
    $history.=$h;
    $history.='</p>';
    
    return $history;
    
}

function message_copy($m,$c,$frtxt,$totxt){
$sql1=mysql_query("SELECT * FROM ".DBP."vk_message WHERE id=".$m);
$res1=mysql_fetch_assoc($sql1);

if (isset($res1['exclude'])) $exclude=$res1['exclude']; else $exclude=0; 



		
		$sql=mysql_query("SELECT max(ord)+1 FROM ".DBP."vk_message WHERE campaign=".$c);
		list($ord)=mysql_fetch_array($sql);

				 
        	mysql_query('INSERT INTO '.DBP.'vk_message(account,campaign,description,text,ord,exclude,main) 
        	VALUES ("","'.mysql_escape_string($c).'","'.mysql_escape_string($res1['description']).'","'.mysql_escape_string(str_replace($frtxt,$totxt,$res1['text'])).'","'.$ord.'","'.$exclude.'","'.$res1['main'].'")');
        	$new_mid = mysql_insert_id();
        	
$sql2=mysql_query("SELECT * FROM ".DBP."vk_replace WHERE message=".$m);
;
		
		while( $res2=mysql_fetch_assoc($sql2))
			{
mysql_query('INSERT INTO '.DBP.'vk_replace(message,textmark,text) 
        						 VALUES ("'.$new_mid.'",  "'.mysql_escape_string($res2['textmark']).'",  "'.mysql_escape_string(str_replace($frtxt,$totxt,$res2['text'])).'") ');//*/
                
                $msg= mysql_error();

			}
//echo '<a href="http://intservis.ru/_adm_/vk-campaign/conf/message.php?id='.$new_mid.'">Просмотреть новое сообщение</a>';

return $new_mid;
}


function uid_transfer($c,$priority,$personal_param1,$personal_param2,$oldsql){
        	
$sql2=mysql_query($oldsql);

$n=mysql_num_rows($sql2);
if ($n) {
		while( $res2=mysql_fetch_assoc($sql2))
			{
if ($personal_param1) $pp1=$personal_param1; else $pp1=$res2['personal_param1'];
if ($personal_param2) $pp2=$personal_param2; else $pp2=$res2['personal_param2'];
			
mysql_query('INSERT INTO '.DBP.'vk_db(vkid,account,personal_param1,personal_param2,priority,sended,exclude,campaign) 
        						 VALUES ("'.mysql_escape_string($res2['vkid']).'",  "'.mysql_escape_string($res2['account']).'",  "'.mysql_escape_string($pp1).'",  "'.mysql_escape_string($pp2).'",  "'.mysql_escape_string($priority).'",  "0",  "'.mysql_escape_string($res2['exclude']).'",  "'.mysql_escape_string($c).'") ');//*/
                


			}

}
return $n;
}


function message_log($vkid,$mid){
mysql_query("INSERT INTO `".DBP."vk_message_log` (vkid,message,senddate) VALUES('".$vkid."','".$mid."','".date('Y-m-d')."')");
}





require_once('full_name.php');


?>
