<?php



//Версия API
if(!defined('VK_API_VERSION')) {
  define('VK_API_VERSION', '5.80'); //Используемая версия API
  define('VK_API_ENDPOINT', 'https://api.vk.com/method/');
}


function sendMessage($chat_id, $message) {
    $method = 'sendMessage';
    $sendData = [
      'text' => $message,
      'chat_id' => $chat_id
    ];
    sendTelegram($method, $sendData);
    return false;
  }



function vkApi_messagesSend($chat_id, $message, $attachments = array()) {


    if(mb_stripos($chat_id,','))
        $chat_id_arr=explode(',',$chat_id);
    else
        $chat_id_arr[0]=$chat_id;
  
    
    $method = 'sendMessage';
    $return_arr=array();
    
    foreach ($chat_id_arr as $chat_id)
    {
        $sendData = [
        'text' => $message,
        'chat_id' => $chat_id,
        'parse_mode'  => 'HTML'
        ];
        
        $return_arr[$chat_id]=sendTelegram($method, $sendData);
    }
        
    return $return_arr;
}
function vkApi_messagesSendButtons($chat_id, $message, $buttons_array, $attachments = array()) {

// 

        // формируем кнопки с колбеками
        $keyboard = [
        "resize_keyboard" => true,
        "inline_keyboard" => [
            [
            [
                'text' => 'Кнопка',
                'callback_data' => 'левую'
            ],
            [
                'text' => 'Кнопка',
                'callback_data' => 'правую'
            ]
            ],
            [
            [
                'text' => 'Кнопка',
                'callback_data' => 'нижнюю'
            ]
            ]
        ]
        ];
        
        
        
        
        $buttons=array();
        $i = 0;
        foreach ($buttons_array as $button_str) {
            $j = 0;
            foreach ($button_str as $button) {
                
                if ($button[0] != null)
                    $buttons[$i][$j]["callback_data"] = $button[0]['payload'];
                $buttons[$i][$j]["text"] = mb_substr($button[1],0,40);//$button[1]; //
                $j++;
            }
            $i++;
        }
        $buttons = [
            "resize_keyboard" => true,
            "inline_keyboard" => $buttons];
               
        
    log_keyboard($chat_id,$buttons_array);
    
    $method = 'sendMessage';
  $sendData = [
    'text' => $message,
    'reply_markup' => $buttons,
    'chat_id' => $chat_id,
    'parse_mode'  => 'HTML'
  ];
    return sendTelegram($method, $sendData);
}


function vkApi_messagesSend_now($user_ids, $message, $attachments = array()) {
  return _vkApi_call('messages.send', array(
    'user_ids'    => $user_ids,
    'message'    => $message,
    'attachment' => implode(',', $attachments)
  ));
}
function vkApi_messagesSendButtons_now($user_ids, $message, $buttons_array, $attachments = array()) {

// 

        $buttons=array();
        $i = 0;
        foreach ($buttons_array as $button_str) {
            $j = 0;
            foreach ($button_str as $button) {
                $color = $button[2];
                $buttons[$i][$j]["action"]["type"] = "text";
                if ($button[0] != null)
                    $buttons[$i][$j]["action"]["payload"] = json_encode($button[0], JSON_UNESCAPED_UNICODE);
                $buttons[$i][$j]["action"]["label"] = mb_substr($button[1],0,40);//$button[1]; //
                $buttons[$i][$j]["color"] = $color;
                $j++;
            }
            $i++;
        }
        $buttons = array(
            "one_time" => false,
            "buttons" => $buttons);
        $buttons = json_encode($buttons, JSON_UNESCAPED_UNICODE);        
        
    log_keyboard($user_ids,$buttons_array);

  return _vkApi_call('messages.send', array(
    'user_ids'    => $user_ids,
    'message'    => $message,
    'attachment' => implode(',', $attachments),    
    'keyboard' => $buttons
  ));
}


function vkApi_usersGet($user_id,$fields_str='') {
    GLOBAL $dblink,$currentGroupId,$data;
    
    $sql=mysqli_query($dblink,"SELECT vkId,first_name,last_name,full_name,username FROM `".DBP_GENERAL."userInfo` WHERE vkId IN ({$user_id})");
    
    $i=0;
    
    if (list($user_info[$i]['id'],$user_info[$i]['first_name'],$user_info[$i]['last_name'],$user_info[$i]['full_name'],$user_info[$i]['username'])=mysqli_fetch_array($sql))
    {
        $i++;
    }
    
    else
    {
        $usersArray=explode(',',$user_id);
        
        
        if(isset($data))
            foreach($usersArray as $vkId)
                mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."userInfo` (vkId,sex,first_name,last_name,full_name,username) VALUES ({$vkId},0,'{$data['chat']['first_name']}','{$data['chat']['last_name']}','".full_name($data['chat']['first_name'])."','{$data['chat']['username']}')");
        else
            foreach($usersArray as $vkId)
                mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."userInfo` (vkId,sex,first_name,last_name,username) VALUES ({$vkId},0,'','','')");
        
        $i=0;
        
        $sql=mysqli_query($dblink,"SELECT vkId,first_name,last_name,username FROM `".DBP_GENERAL."userInfo` WHERE vkId IN ({$user_id})");
            
    }
    
    while(list($user_info[$i]['id'],$user_info[$i]['first_name'],$user_info[$i]['last_name'],$user_info[$i]['username'])=mysqli_fetch_array($sql))
        $i++;

  
  return $user_info;
}

function _vkApi_call($method, $params = array()) {
  GLOBAL $token,$service_token;
  
  return 1;
  
  if(!isset($token))
    $token=$service_token;

  $params['access_token'] = $token;
  $params['v'] = VK_API_VERSION;
  $query = http_build_query($params);
  $url = VK_API_ENDPOINT.$method;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL,$url);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
  
  //echo VK_API_ENDPOINT.$method.'?'.$query;
  
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $json = curl_exec($curl);
  $error = curl_error($curl);
  if ($error) {
    log_error($error);
    //throw new Exception("Failed {$method} request");
    return $error;
  }
  curl_close($curl);
  $response = json_decode($json, true);
  if (!$response || !isset($response['response'])) {
    log_error($json);
    //throw new Exception("Invalid response for {$method} request");
    return $response['error'];
  }
  return $response['response'];
}




function sendTelegram($method, $data, $headers = []) {

    GLOBAL $token;
    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => 'https://api.telegram.org/bot'. $token .'/'.$method,
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => array_merge(["Content-Type: application/json"], $headers)
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }
